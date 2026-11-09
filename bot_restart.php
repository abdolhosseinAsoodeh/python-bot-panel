<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
stopBotProcess($project);
sleep(1);

$fileParam = isset($_GET['file']) ? '&file=' . urlencode($_GET['file']) : '';
header('Location: bot_start.php?project=' . urlencode($project) . $fileParam);
exit;
