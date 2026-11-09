<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
stopBotProcess($project);

header('Location: dashboard.php?project=' . urlencode($project));
exit;
