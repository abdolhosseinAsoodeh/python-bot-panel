<?php
require_once __DIR__ . '/functions.php';

verifyCsrf();

$isProject = !empty($_POST['is_project']);
$name = cleanName($_POST['name'] ?? '');

if ($isProject) {
    if ($name !== '') {
        ensureProject($name);
    }
    header('Location: dashboard.php?project=' . urlencode($name));
    exit;
}

$project = projectName();
$path = trim($_POST['path'] ?? '', '/');

try {
    $directory = safePath($project, $path);
} catch (Exception $e) {
    die('Invalid directory path.');
}

$newFolder = $directory . DIRECTORY_SEPARATOR . $name;
if (!file_exists($newFolder)) {
    @mkdir($newFolder, 0755, true);
}

header('Location: files.php?project=' . urlencode($project) . '&path=' . urlencode($path));
exit;
