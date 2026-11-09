<?php
require_once __DIR__ . '/functions.php';

verifyCsrf();

$project = projectName();
$path = isset($_POST['path']) ? trim($_POST['path'], '/') : '';

try {
    $directory = safePath($project, $path);
} catch (Exception $e) {
    die('Invalid target path.');
}

if (!isset($_FILES['files'])) {
    header('Location: files.php?project=' . urlencode($project) . '&path=' . urlencode($path));
    exit;
}

$files = $_FILES['files'];
$count = is_array($files['name']) ? count($files['name']) : 0;

for ($i = 0; $i < $count; $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    if ($files['size'][$i] > MAX_UPLOAD_SIZE) {
        continue;
    }

    $name = cleanName($files['name'][$i]);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    $target = $directory . DIRECTORY_SEPARATOR . $name;
    if (file_exists($target)) {
        $target = $directory . DIRECTORY_SEPARATOR .
            pathinfo($name, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
    }

    if (move_uploaded_file($files['tmp_name'][$i], $target)) {
        if ($ext === 'zip') {
            unzipFile($target, $directory);
        }
    }
}

header('Location: files.php?project=' . urlencode($project) . '&path=' . urlencode($path));
exit;
