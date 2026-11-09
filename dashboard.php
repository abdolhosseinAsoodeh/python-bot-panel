<?php
require_once __DIR__ . '/functions.php';

$projects = getAllProjects();
$selected = projectName();
ensureProject($selected);

$running = botRunning($selected);
$allPythonFiles = findAllPythonFiles($selected);
$pythonFile = findPythonFile($selected);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telegram Bot Panel</title>
    <link rel="stylesheet" href="views/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">🤖 Bot Panel</div>
        <a href="dashboard.php?project=<?= urlencode($selected) ?>" class="menu active">🏠 Dashboard</a>
        <a href="files.php?project=<?= urlencode($selected) ?>" class="menu">📁 Files</a>
        <a href="terminal.php?project=<?= urlencode($selected) ?>" class="menu">🖥 Terminal</a>
        <a href="logs.php?project=<?= urlencode($selected) ?>" class="menu">📜 Logs</a>
    </aside>

    <main class="content">
        <div class="topbar">
            <h1>Dashboard</h1>
            <div style="display:flex; gap:10px; align-items:center;">
                <form method="get" action="dashboard.php" style="display:flex; gap:8px;">
                    <select name="project" onchange="this.form.submit()">
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= htmlspecialchars($project) ?>" <?= $project === $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <form method="post" action="mkdir.php" style="display:inline-flex; gap:5px;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="project" value="default">
                    <input type="hidden" name="path" value="">
                    <input type="hidden" name="is_project" value="1">
                    <input type="text" name="name" placeholder="New bot name" required style="width:130px; padding:8px;">
                    <button class="btn" type="submit">+ Bot</button>
                </form>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="card-icon">🤖</div>
                <div>
                    <span>Selected Bot</span>
                    <strong><?= htmlspecialchars($selected) ?></strong>
                </div>
            </div>

            <div class="card">
                <div class="card-icon"><?= $running ? '🟢' : '🔴' ?></div>
                <div>
                    <span>Status</span>
                    <strong><?= $running ? 'Running' : 'Stopped' ?></strong>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">🐍</div>
                <div>
                    <span>Main Script</span>
                    <strong><?= $pythonFile ? htmlspecialchars($pythonFile) : 'Not found' ?></strong>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2>Bot Control</h2>
            <div class="actions" style="align-items:center;">
                <?php if ($running): ?>
                    <a class="btn danger" href="bot_stop.php?project=<?= urlencode($selected) ?>&csrf_token=<?= csrfToken() ?>">
                        ⏹ Stop Bot
                    </a>
                    <a class="btn warning" href="bot_restart.php?project=<?= urlencode($selected) ?>&csrf_token=<?= csrfToken() ?>">
                        🔄 Restart Bot
                    </a>
                <?php else: ?>
                    <?php if (count($allPythonFiles) > 1): ?>
                        <form method="get" action="bot_start.php" style="display:inline-flex; gap:8px;">
                            <input type="hidden" name="project" value="<?= htmlspecialchars($selected) ?>">
                            <select name="file">
                                <?php foreach ($allPythonFiles as $f): ?>
                                    <option value="<?= htmlspecialchars($f) ?>" <?= $f === $pythonFile ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn success" type="submit">▶ Start Bot</button>
                        </form>
                    <?php elseif ($pythonFile): ?>
                        <a class="btn success" href="bot_start.php?project=<?= urlencode($selected) ?>&file=<?= urlencode($pythonFile) ?>">
                            ▶ Start Bot
                        </a>
                    <?php else: ?>
                        <span style="color:#ef4444; font-size:14px;">Upload a .py file in File Manager first.</span>
                    <?php endif; ?>
                <?php endif; ?>

                <a class="btn" href="logs.php?project=<?= urlencode($selected) ?>">📜 View Logs</a>
            </div>
        </div>

        <div class="panel">
            <h2>Quick Access</h2>
            <div class="quick-grid">
                <a href="files.php?project=<?= urlencode($selected) ?>">
                    📁
                    <span>File Manager</span>
                </a>
                <a href="terminal.php?project=<?= urlencode($selected) ?>">
                    🖥
                    <span>Terminal</span>
                </a>
                <a href="logs.php?project=<?= urlencode($selected) ?>">
                    📜
                    <span>Bot Logs</span>
                </a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
