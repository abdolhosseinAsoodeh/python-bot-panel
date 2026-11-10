<?php
require_once __DIR__ . '/functions.php';

$projects = getAllProjects();
$selected = projectName();
ensureProject($selected);

$running = botRunning($selected);
$allPythonFiles = findAllPythonFiles($selected);
$pythonFile = findPythonFile($selected);
$pid = $running ? getBotPid($selected) : null;
$startedAt = $running ? botStartedAt($selected) : null;
$stats = projectStats($selected);
$logFile = getBotLogFile($selected);
$logTail = tailFile($logFile, 18);
$logUpdated = is_file($logFile) ? filemtime($logFile) : null;
$q = '?project=' . urlencode($selected);
$csrf = urlencode(csrfToken());

renderHeader('Dashboard', 'dashboard', $selected);
?>
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <div class="page-sub">Manage, run and monitor your Python bots.</div>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" type="button" data-dialog-open="new-bot-dialog"><?= icon('plus', 16) ?> New bot</button>
            </div>
        </div>

        <section class="hero <?= $running ? 'is-running' : 'is-stopped' ?>">
            <div class="hero-main">
                <div class="hero-avatar"><?= icon('bot', 28) ?></div>
                <div style="min-width:0">
                    <div class="hero-name">
                        <?= e($selected) ?>
                        <span class="pill <?= $running ? 'pill-on' : 'pill-off' ?>">
                            <span class="dot <?= $running ? 'dot-on' : 'dot-off' ?>"></span>
                            <?= $running ? 'Running' : 'Stopped' ?>
                        </span>
                    </div>
                    <div class="hero-meta">
                        <span><?= icon('code', 14) ?> <?= $pythonFile ? e($pythonFile) : 'No script yet' ?></span>
                        <?php if ($pid): ?><span><?= icon('activity', 14) ?> PID <?= (int)$pid ?></span><?php endif; ?>
                        <?php if ($startedAt): ?><span><?= icon('clock', 14) ?> Started <?= e(timeAgo($startedAt)) ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="hero-actions">
                <?php if ($running): ?>
                    <a class="btn btn-warning" href="bot_restart.php<?= $q ?>&csrf_token=<?= $csrf ?>"><?= icon('restart', 16) ?> Restart</a>
                    <a class="btn btn-danger" href="bot_stop.php<?= $q ?>&csrf_token=<?= $csrf ?>" data-confirm="This will stop “<?= e($selected) ?>”."><?= icon('stop', 16) ?> Stop</a>
                <?php elseif (count($allPythonFiles) > 1): ?>
                    <form method="get" action="bot_start.php" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <input type="hidden" name="project" value="<?= e($selected) ?>">
                        <select name="file" aria-label="Script to run">
                            <?php foreach ($allPythonFiles as $f): ?>
                                <option value="<?= e($f) ?>" <?= $f === $pythonFile ? 'selected' : '' ?>><?= e($f) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-success btn-lg" type="submit"><?= icon('play', 16) ?> Start bot</button>
                    </form>
                <?php elseif ($pythonFile): ?>
                    <a class="btn btn-success btn-lg" href="bot_start.php<?= $q ?>&file=<?= urlencode($pythonFile) ?>"><?= icon('play', 16) ?> Start bot</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="files.php<?= $q ?>"><?= icon('upload', 16) ?> Upload your bot files</a>
                <?php endif; ?>
            </div>
        </section>

        <div class="stats">
            <div class="stat">
                <div class="stat-icon <?= $running ? 'green' : 'red' ?>"><?= icon('activity', 20) ?></div>
                <div><div class="stat-label">Status</div><div class="stat-value"><?= $running ? 'Online' : 'Offline' ?></div></div>
            </div>
            <div class="stat">
                <div class="stat-icon"><?= icon('code', 20) ?></div>
                <div><div class="stat-label">Python scripts</div><div class="stat-value"><?= count($allPythonFiles) ?></div></div>
            </div>
            <div class="stat">
                <div class="stat-icon cyan"><?= icon('file', 20) ?></div>
                <div><div class="stat-label">Files</div><div class="stat-value"><?= (int)$stats['files'] ?></div></div>
            </div>
            <div class="stat">
                <div class="stat-icon amber"><?= icon('hard-drive', 20) ?></div>
                <div><div class="stat-label">Disk usage</div><div class="stat-value"><?= e(formatSize((int)$stats['size'])) ?></div></div>
            </div>
        </div>

        <div class="grid-2">
            <section class="panel">
                <div class="panel-head">
                    <div class="panel-title"><?= icon('logs', 16) ?> Recent output</div>
                    <div class="page-actions">
                        <?php if ($logUpdated): ?><span class="muted" style="font-size:12px">Updated <?= e(timeAgo($logUpdated)) ?></span><?php endif; ?>
                        <a class="btn btn-sm" href="logs.php<?= $q ?>">Open logs <?= icon('chevron-right', 14) ?></a>
                    </div>
                </div>
                <?php if ($logTail !== ''): ?>
                    <pre class="console log-preview" id="log-preview"><?= e($logTail) ?></pre>
                <?php else: ?>
                    <div class="empty">
                        <div class="empty-icon"><?= icon('logs', 24) ?></div>
                        <h3>No output yet</h3>
                        <p>Start the bot and its output will show up here.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <div class="panel-title"><?= icon('bot', 16) ?> Your bots</div>
                    <span class="tag"><?= count($projects) ?></span>
                </div>
                <ul class="bot-list">
                    <?php foreach ($projects as $p): $on = $p === $selected ? $running : botRunning($p); ?>
                        <li>
                            <a href="dashboard.php?project=<?= urlencode($p) ?>" class="<?= $p === $selected ? 'current' : '' ?>">
                                <span class="dot <?= $on ? 'dot-on' : 'dot-off' ?>"></span>
                                <span class="bot-name"><?= e($p) ?></span>
                                <span class="bot-state"><?= $on ? 'Running' : 'Stopped' ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>

        <section class="panel" style="margin-top:20px">
            <div class="panel-head"><div class="panel-title">Quick access</div></div>
            <div class="panel-body">
                <div class="quick-grid">
                    <a class="quick" href="files.php<?= $q ?>">
                        <span class="stat-icon amber"><?= icon('folder', 18) ?></span>
                        <span><strong>File manager</strong><small>Upload, edit, organise</small></span>
                    </a>
                    <a class="quick" href="terminal.php<?= $q ?>">
                        <span class="stat-icon"><?= icon('terminal', 18) ?></span>
                        <span><strong>Terminal</strong><small>pip install &amp; commands</small></span>
                    </a>
                    <a class="quick" href="logs.php<?= $q ?>">
                        <span class="stat-icon cyan"><?= icon('logs', 18) ?></span>
                        <span><strong>Live logs</strong><small>Follow bot output</small></span>
                    </a>
                </div>
            </div>
        </section>

<script>
(function () {
    var el = document.getElementById('log-preview');
    if (el) el.scrollTop = el.scrollHeight;
})();
</script>
<?php renderFooter(); ?>
