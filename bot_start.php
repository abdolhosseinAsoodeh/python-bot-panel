<?php
require_once __DIR__ . '/functions.php';

$project = projectName();

if (botRunning($project)) {
    header('Location: dashboard.php?project=' . urlencode($project));
    exit;
}

$selectedFile = $_GET['file'] ?? $_POST['file'] ?? null;
$pythonFile = findPythonFile($project, $selectedFile);

if (!$pythonFile) {
    die('No Python file found in this project. Please upload or create a .py file first.');
}

$projectDir = projectPath($project);
$fullScriptPath = safePath($project, $pythonFile);
$logFile = getBotLogFile($project);
$pidFile = getPidFile($project);
$launcher = __DIR__ . DIRECTORY_SEPARATOR . 'launcher.py';

if (isWindows()) {
    $command = 'start "" /B ' . PYTHON_COMMAND . ' "' . $launcher . '" "' . $projectDir . '" "' . $fullScriptPath . '" "' . $logFile . '" "' . $pidFile . '" "' . PYTHON_COMMAND . '"';
    pclose(popen($command, 'r'));
} else {
    // Linux / macOS: execute launcher in background with nohup
    $cmd = sprintf(
        'nohup %s %s %s %s %s %s %s > /dev/null 2>&1 &',
        escapeshellcmd(PYTHON_COMMAND),
        escapeshellarg($launcher),
        escapeshellarg($projectDir),
        escapeshellarg($fullScriptPath),
        escapeshellarg($logFile),
        escapeshellarg($pidFile),
        escapeshellarg(PYTHON_COMMAND)
    );
    shell_exec($cmd);
}

// Give process half a second to initialize PID file
usleep(400000);

header('Location: dashboard.php?project=' . urlencode($project));
exit;
