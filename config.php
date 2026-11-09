<?php
// Telegram Bot Management Panel - Configuration

if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
}

define('STORAGE_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'storage');
define('PROJECTS_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'projects');
define('LOGS_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'logs');
define('TEMP_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'temp');

define('MAX_UPLOAD_SIZE', 200 * 1024 * 1024); // 200 MB limit

// Auto-detect Python binary if not explicitly set
if (!defined('PYTHON_COMMAND')) {
    $pythonCmd = 'python3';
    if (stripos(PHP_OS, 'WIN') === 0) {
        $pythonCmd = 'python';
    }
    define('PYTHON_COMMAND', $pythonCmd);
}

// Session security defaults
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Ensure required directories exist
foreach ([STORAGE_DIR, PROJECTS_DIR, LOGS_DIR, TEMP_DIR] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}
