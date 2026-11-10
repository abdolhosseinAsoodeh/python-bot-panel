<?php
require_once __DIR__ . '/functions.php';

// Stream command output as soon as the process writes it.
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '1');
while (ob_get_level() > 0) { @ob_end_flush(); }
ob_implicit_flush(true);

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Accel-Buffering: no');

verifyCsrf();

$project = projectName();
$command = trim($_POST['command'] ?? '');

if ($command === '') {
    exit;
}

if ($command === 'clear_logs') {
    $logFile = getBotLogFile($project);
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
    }
    echo "Logs cleared.";
    flush();
    exit;
}

if ($command === 'help') {
    echo "Available commands in this shell:\n";
    echo "  pip install <package>    Install python package\n";
    echo "  python <script>          Execute python script\n";
    echo "  ls / dir                 List files in project\n";
    echo "  pwd                      Current directory\n";
    echo "  cat / type <file>        View text file\n";
    echo "  ps                       Process status\n";
    echo "  clear                    Clear screen\n";
    flush();
    exit;
}

if ($command === 'clear') {
    echo "\033[2J\033[H";
    flush();
    exit;
}

$directory = projectPath($project);
if (!is_dir($directory)) {
    http_response_code(404);
    echo "Project directory not found.\n";
    flush();
    exit;
}

$oldDirectory = getcwd();
if (!@chdir($directory)) {
    http_response_code(500);
    echo "Cannot access project directory.\n";
    flush();
    exit;
}

// Keep the existing command behavior, but use proc_open instead of exec so
// stdout/stderr can be read while the command is still running.
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

// On Windows an argv array gets re-quoted as a single word, so cmd.exe would
// treat the whole line as one program name. Pass a raw command string instead.
if (isWindows()) {
    $process = @proc_open('cmd /s /c "' . $command . '"', $descriptors, $pipes, $directory, null, ['bypass_shell' => true]);
} else {
    $process = @proc_open(['/bin/sh', '-c', $command], $descriptors, $pipes, $directory);
}
if (!is_resource($process)) {
    @chdir($oldDirectory);
    http_response_code(500);
    echo "Could not start process.\n";
    flush();
    exit;
}

fclose($pipes[0]);
stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);

$openPipes = [1 => $pipes[1], 2 => $pipes[2]];
while (!empty($openPipes)) {
    $read = array_values($openPipes);
    $write = null;
    $except = null;
    $changed = @stream_select($read, $write, $except, 1);

    if ($changed === false) {
        // Fallback if stream_select is unsupported by this PHP environment.
        foreach ($openPipes as $id => $pipe) {
            $chunk = fread($pipe, 8192);
            if ($chunk !== false && $chunk !== '') {
                echo $chunk;
                flush();
            }
            if (feof($pipe)) {
                fclose($pipe);
                unset($openPipes[$id]);
            }
        }
        usleep(100000);
        continue;
    }

    foreach ($read as $ready) {
        $id = ($ready === $pipes[1]) ? 1 : 2;
        $chunk = fread($ready, 8192);
        if ($chunk !== false && $chunk !== '') {
            echo $chunk;
            flush();
        }
        if (feof($ready)) {
            fclose($ready);
            unset($openPipes[$id]);
        }
    }
}

$returnCode = proc_close($process);
@chdir($oldDirectory);
echo "\n\n[Exit Code: " . $returnCode . "]";
flush();
