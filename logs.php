<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
$logFile = getBotLogFile($project);
$isAjax = isset($_GET['ajax']);

$log = '';
if (file_exists($logFile)) {
    $log = file_get_contents($logFile);
    if (strlen($log) > 500000) {
        $log = substr($log, -500000);
    }
} else {
    $log = "No logs generated yet for project [{$project}].";
}

if ($isAjax) {
    header('Content-Type: text/plain; charset=utf-8');
    echo $log;
    exit;
}

$projects = getAllProjects();
$running = botRunning($project);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logs - <?= htmlspecialchars($project) ?></title>
    <link rel="stylesheet" href="views/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">🤖 Bot Panel</div>
        <a class="menu" href="dashboard.php?project=<?= urlencode($project) ?>">🏠 Dashboard</a>
        <a class="menu" href="files.php?project=<?= urlencode($project) ?>">📁 Files</a>
        <a class="menu" href="terminal.php?project=<?= urlencode($project) ?>">🖥 Terminal</a>
        <a class="menu active" href="logs.php?project=<?= urlencode($project) ?>">📜 Logs</a>
    </aside>

    <main class="content">
        <div class="topbar">
            <h1>📜 Bot Logs: <?= htmlspecialchars($project) ?></h1>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="badge <?= $running ? 'badge-running' : 'badge-stopped' ?>">
                    <?= $running ? '● Running' : '○ Stopped' ?>
                </span>
                <button class="btn" onclick="fetchLogs()">🔄 Refresh</button>
                <button class="btn danger" onclick="clearLogs()">🗑 Clear</button>
            </div>
        </div>

        <div class="log-container">
            <pre id="log-output"><?= htmlspecialchars($log) ?></pre>
        </div>
    </main>
</div>

<script>
async function fetchLogs() {
    try {
        const res = await fetch('logs.php?project=<?= urlencode($project) ?>&ajax=1');
        const text = await res.text();
        const el = document.getElementById('log-output');
        el.textContent = text;
        el.parentElement.scrollTop = el.parentElement.scrollHeight;
    } catch(e) {
        console.error(e);
    }
}

async function clearLogs() {
    if (!confirm('Clear bot log file?')) return;
    try {
        await fetch('api_terminal.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'project=' + encodeURIComponent('<?= $project ?>') + '&command=clear_logs&csrf_token=<?= csrfToken() ?>'
        });
        fetchLogs();
    } catch(e) {}
}

// Auto-refresh every 3 seconds if bot is running
<?php if ($running): ?>
setInterval(fetchLogs, 3000);
<?php endif; ?>
window.onload = function() {
    const el = document.querySelector('.log-container');
    if (el) el.scrollTop = el.scrollHeight;
};
</script>
</body>
</html>
