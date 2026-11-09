<?php
require_once __DIR__ . '/functions.php';

verifyCsrf();

$project = projectName();
$file = $_GET['file'] ?? '';

try {
    $path = safePath($project, $file);
} catch (Exception $e) {
    die('Invalid path.');
}

if (is_dir($path)) {
    deleteDirectory($path);
} elseif (is_file($path)) {
    @unlink($path);
}

$parent = dirname($file);
if ($parent === '.') {
    $parent = '';
}

header('Location: files.php?project=' . urlencode($project) . '&path=' . urlencode($parent));
exit;
