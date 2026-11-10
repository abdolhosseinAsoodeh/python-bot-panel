<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/views/layout.php';

// Cross-Platform OS check
function isWindows(): bool
{
    return stripos(PHP_OS, 'WIN') === 0;
}

// CSRF Protection
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('Invalid CSRF token.');
    }
    return true;
}

// Sanitize names for files and projects
function cleanName(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[^\p{L}\p{N}\._\- ]/u', '_', $name);
    $name = trim($name, '. ');
    return $name !== '' ? $name : 'item';
}

function projectName(): string
{
    $project = $_GET['project'] ?? $_POST['project'] ?? 'default';
    $project = cleanName((string)$project);
    return $project !== '' ? $project : 'default';
}

function projectPath(?string $project = null): string
{
    if ($project === null) {
        $project = projectName();
    }
    return PROJECTS_DIR . DIRECTORY_SEPARATOR . $project;
}

function ensureProject(string $project): string
{
    $path = projectPath($project);
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
    return $path;
}

function getAllProjects(): array
{
    $projects = [];
    if (is_dir(PROJECTS_DIR)) {
        $items = scandir(PROJECTS_DIR);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (is_dir(PROJECTS_DIR . DIRECTORY_SEPARATOR . $item)) {
                $projects[] = $item;
            }
        }
    }
    if (empty($projects)) {
        $projects[] = 'default';
        ensureProject('default');
    }
    return $projects;
}

function safePath(string $project, string $relative = ''): string
{
    $base = realpath(ensureProject($project));
    if ($base === false) {
        throw new RuntimeException('Project directory not found.');
    }

    $relative = str_replace('\\', '/', $relative);
    $relative = trim($relative, '/');

    if ($relative === '') {
        return $base;
    }

    $parts = explode('/', $relative);
    $safeParts = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            throw new RuntimeException('Directory traversal attempt detected.');
        }
        $safeParts[] = $part;
    }

    $target = $base . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $safeParts);

    // Verify target does not escape base directory
    $realTarget = realpath($target);
    if ($realTarget !== false) {
        if (strpos($realTarget, $base) !== 0) {
            throw new RuntimeException('Access denied: path escapes project root.');
        }
        return $realTarget;
    }

    // For new files/folders that don't exist yet, check realpath of existing parent
    $parent = dirname($target);
    $realParent = realpath($parent);
    if ($realParent === false || strpos($realParent, $base) !== 0) {
        throw new RuntimeException('Invalid target directory.');
    }

    return $target;
}

function formatSize(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    if ($bytes < 1024 * 1024 * 1024) {
        return round($bytes / 1024 / 1024, 2) . ' MB';
    }
    return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
}

function isTextFile(string $file): bool
{
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $extensions = [
        'py', 'php', 'js', 'ts', 'json', 'txt', 'html', 'htm', 'css', 'scss',
        'xml', 'yaml', 'yml', 'ini', 'env', 'md', 'sql', 'sh', 'bat', 'cmd',
        'log', 'csv', 'conf', 'toml', 'cfg', 'dockerfile', 'gitignore'
    ];
    return in_array($extension, $extensions, true);
}

function deleteDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }
    return @rmdir($dir);
}

function unzipFile(string $zipFile, string $destination): bool
{
    if (!class_exists('ZipArchive')) {
        return false;
    }
    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) {
        return false;
    }
    if (!is_dir($destination)) {
        @mkdir($destination, 0755, true);
    }
    
    // Prevent zip slip vulnerability during extraction
    $realDest = realpath($destination);
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);
        $entryName = str_replace('\\', '/', $entryName);
        if (strpos($entryName, '../') !== false || strpos($entryName, '..\\') !== false) {
            $zip->close();
            return false;
        }
    }

    $result = $zip->extractTo($destination);
    $zip->close();
    return $result;
}

function getBotLogFile(string $project): string
{
    return LOGS_DIR . DIRECTORY_SEPARATOR . $project . '.log';
}

function getPidFile(string $project): string
{
    return TEMP_DIR . DIRECTORY_SEPARATOR . $project . '.pid';
}

function getBotPid(string $project): ?int
{
    $pidFile = getPidFile($project);
    if (!file_exists($pidFile)) {
        return null;
    }
    $pid = trim((string)file_get_contents($pidFile));
    return is_numeric($pid) && (int)$pid > 0 ? (int)$pid : null;
}

function botRunning(string $project): bool
{
    $pid = getBotPid($project);
    if (!$pid) {
        return false;
    }

    if (isWindows()) {
        exec('tasklist /FI "PID eq ' . (int)$pid . '" /NH', $output);
        foreach ($output as $line) {
            if (strpos($line, (string)$pid) !== false) {
                return true;
            }
        }
    } else {
        // POSIX / Linux / macOS
        if (function_exists('posix_kill')) {
            if (posix_kill($pid, 0)) {
                return true;
            }
        } else {
            exec('ps -p ' . (int)$pid, $output, $result);
            if ($result === 0 && count($output) > 1) {
                return true;
            }
        }
    }

    // Process is no longer running, clean up stale PID file
    @unlink(getPidFile($project));
    return false;
}

function stopBotProcess(string $project): bool
{
    $pid = getBotPid($project);
    if ($pid) {
        if (isWindows()) {
            exec('taskkill /PID ' . (int)$pid . ' /T /F 2>&1');
        } else {
            // Kill entire process group / children on Linux
            exec('kill -TERM ' . (int)$pid . ' 2>&1');
            usleep(200000); // 200ms
            if (botRunning($project)) {
                exec('kill -9 ' . (int)$pid . ' 2>&1');
            }
        }
    }

    $pidFile = getPidFile($project);
    if (file_exists($pidFile)) {
        @unlink($pidFile);
    }
    return true;
}

function botStartedAt(string $project): ?int
{
    $pidFile = getPidFile($project);
    return file_exists($pidFile) ? (int)filemtime($pidFile) : null;
}

function projectStats(string $project): array
{
    $stats = ['files' => 0, 'size' => 0];
    $base = projectPath($project);
    if (!is_dir($base)) {
        return $stats;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $stats['files']++;
            $stats['size'] += $item->getSize();
        }
    }
    return $stats;
}

function tailFile(string $file, int $lines = 20): string
{
    if (!is_file($file) || filesize($file) === 0) {
        return '';
    }
    $fp = fopen($file, 'rb');
    $size = filesize($file);
    $chunk = min($size, 64 * 1024);
    fseek($fp, -$chunk, SEEK_END);
    $data = fread($fp, $chunk);
    fclose($fp);
    $all = preg_split('/\r\n|\n|\r/', rtrim($data));
    return implode("\n", array_slice($all, -$lines));
}

function findAllPythonFiles(string $project): array
{
    $base = projectPath($project);
    if (!is_dir($base)) {
        return [];
    }
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        if ($item->isFile() && strtolower($item->getExtension()) === 'py') {
            $rel = str_replace([$base . DIRECTORY_SEPARATOR, $base . '/'], '', $item->getPathname());
            $files[] = str_replace('\\', '/', $rel);
        }
    }
    sort($files);
    return $files;
}

function findPythonFile(string $project, ?string $preferred = null): ?string
{
    $base = projectPath($project);
    if (!is_dir($base)) {
        return null;
    }

    if ($preferred !== null && $preferred !== '') {
        $candidate = safePath($project, $preferred);
        if (is_file($candidate) && strtolower(pathinfo($candidate, PATHINFO_EXTENSION)) === 'py') {
            return $preferred;
        }
    }

    // Check main.py, bot.py, app.py first
    $priorities = ['main.py', 'bot.py', 'app.py', 'run.py', 'index.py'];
    foreach ($priorities as $pri) {
        if (is_file($base . DIRECTORY_SEPARATOR . $pri)) {
            return $pri;
        }
    }

    $all = findAllPythonFiles($project);
    return !empty($all) ? $all[0] : null;
}
