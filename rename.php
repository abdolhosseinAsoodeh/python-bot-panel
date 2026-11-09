<?php
require_once __DIR__ . '/functions.php';

verifyCsrf();

$project = projectName();
$file = trim($_POST['file'] ?? '', '/');
$name = cleanName($_POST['name'] ?? '');

try {
    $oldPath = safePath($project, $file);
} catch (Exception $e) {
    die('Invalid path.');
}

$newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . $name;

// Prevent overwriting
if (file_exists($oldPath) && !file_exists($newPath)) {
    rename($oldPath, $newPath);
}

$parent = dirname($file);
if ($parent === '.') {
    $parent = '';
}

header('Location: files.php?project=' . urlencode($project) . '&path=' . urlencode($parent));
exit;
