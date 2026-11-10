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
}

if ($isAjax) {
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Bot-Running: ' . (botRunning($project) ? '1' : '0'));
    echo $log;
    exit;
}

$running = botRunning($project);
$size = is_file($logFile) ? filesize($logFile) : 0;

renderHeader('Logs', 'logs', $project);
?>
        <div class="page-header">
            <div>
                <h1 class="page-title">Logs</h1>
                <div class="page-sub">
                    <span class="pill <?= $running ? 'pill-on' : 'pill-off' ?>" id="status-pill">
                        <span class="dot <?= $running ? 'dot-on' : 'dot-off' ?>"></span>
                        <span id="status-text"><?= $running ? 'Running' : 'Stopped' ?></span>
                    </span>
                    <span><?= e($project) ?>.log · <span id="log-size"><?= e(formatSize((int)$size)) ?></span></span>
                </div>
            </div>
            <div class="page-actions">
                <input type="search" id="log-filter" placeholder="Filter lines…" aria-label="Filter log lines" style="width:180px">
                <button class="btn" type="button" id="log-refresh"><?= icon('refresh', 16) ?> Refresh</button>
                <button class="btn btn-danger" type="button" id="log-clear"><?= icon('trash', 16) ?> Clear</button>
            </div>
        </div>

        <section class="panel" style="overflow:hidden">
            <div class="panel-head">
                <div class="page-actions">
                    <label class="toggle"><input type="checkbox" id="opt-live" checked><span class="track"></span> Live</label>
                    <label class="toggle"><input type="checkbox" id="opt-follow" checked><span class="track"></span> Auto-scroll</label>
                </div>
                <button class="btn btn-sm btn-ghost" type="button" id="log-bottom"><?= icon('arrow-down', 14) ?> Jump to end</button>
            </div>
            <div class="console log-view" id="log-view"></div>
        </section>

<dialog class="dialog" id="clear-dialog">
    <form method="dialog">
        <div class="dialog-head">
            <div class="dialog-icon danger"><?= icon('trash', 20) ?></div>
            <div><h3>Clear log file?</h3><p>All output saved for “<?= e($project) ?>” will be erased.</p></div>
        </div>
        <div class="dialog-actions">
            <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
            <button class="btn btn-danger" type="button" id="clear-go">Clear log</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    var PROJECT = <?= json_encode($project) ?>;
    var CSRF = <?= json_encode(csrfToken()) ?>;
    var initial = <?= json_encode($log, JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var view = document.getElementById('log-view');
    var filter = document.getElementById('log-filter');
    var live = document.getElementById('opt-live');
    var follow = document.getElementById('opt-follow');
    var last = null;

    function classify(line) {
        if (/^=== |^\[Launcher|^(Project|Script|Interpreter|PID):/.test(line)) return 'l-sys';
        if (/(error|exception|traceback|critical|fatal)/i.test(line)) return 'l-error';
        if (/warn/i.test(line)) return 'l-warn';
        return '';
    }

    function applyFilter() {
        var q = filter.value.toLowerCase();
        view.querySelectorAll('.log-line').forEach(function (el) {
            el.classList.toggle('hidden', q !== '' && el.textContent.toLowerCase().indexOf(q) === -1);
        });
    }

    function render(text) {
        if (text === last) return;
        last = text;
        view.innerHTML = '';
        if (!text.trim()) {
            view.innerHTML = '<div class="log-empty">No logs yet. Start the bot to see its output here.</div>';
            return;
        }
        var frag = document.createDocumentFragment();
        var lines = text.replace(/^\s*\n/, '').replace(/\s+$/, '').split(/\r?\n/);
        for (var i = 0; i < lines.length; i++) {
            var div = document.createElement('div');
            div.className = 'log-line ' + classify(lines[i]);
            div.textContent = lines[i] || ' ';
            frag.appendChild(div);
        }
        view.appendChild(frag);
        applyFilter();
        if (follow.checked) view.scrollTop = view.scrollHeight;
    }

    function setStatus(on) {
        var pill = document.getElementById('status-pill');
        pill.className = 'pill ' + (on ? 'pill-on' : 'pill-off');
        pill.querySelector('.dot').className = 'dot ' + (on ? 'dot-on' : 'dot-off');
        document.getElementById('status-text').textContent = on ? 'Running' : 'Stopped';
    }

    function fmt(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    async function fetchLogs() {
        try {
            var res = await fetch('logs.php?project=' + encodeURIComponent(PROJECT) + '&ajax=1', { cache: 'no-store' });
            var text = await res.text();
            setStatus(res.headers.get('X-Bot-Running') === '1');
            document.getElementById('log-size').textContent = fmt(new Blob([text]).size);
            render(text);
        } catch (e) {}
    }

    render(initial);

    setInterval(function () {
        if (live.checked && !document.hidden) fetchLogs();
    }, 3000);

    filter.addEventListener('input', applyFilter);
    document.getElementById('log-refresh').addEventListener('click', function () { last = null; fetchLogs(); });
    document.getElementById('log-bottom').addEventListener('click', function () { view.scrollTop = view.scrollHeight; });

    var dlg = document.getElementById('clear-dialog');
    document.getElementById('log-clear').addEventListener('click', function () { dlg.showModal(); });
    document.getElementById('clear-go').addEventListener('click', async function () {
        dlg.close();
        var body = new URLSearchParams({ project: PROJECT, command: 'clear_logs', csrf_token: CSRF });
        await fetch('api_terminal.php', { method: 'POST', body: body });
        last = null;
        fetchLogs();
        toast('Log cleared');
    });
})();
</script>
<?php renderFooter(); ?>
