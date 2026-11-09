<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
$file = $_GET['file'] ?? '';

try {
    $path = safePath($project, $file);
} catch (Exception $e) {
    die('Invalid path.');
}

if (!is_file($path)) {
    die('File not found.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($path));

readfile($path);
exit;
