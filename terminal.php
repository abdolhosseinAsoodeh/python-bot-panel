<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
ensureProject($project);

$py = PYTHON_COMMAND;
$quick = [
    ['package', $py . ' -m pip install -r requirements.txt --user'],
    ['package', $py . ' -m pip list --user'],
    ['code', $py . ' --version'],
    ['folder', isWindows() ? 'dir' : 'ls -la'],
];

renderHeader('Terminal', 'terminal', $project);
?>
        <div class="page-header">
            <div>
                <h1 class="page-title">Terminal</h1>
                <div class="page-sub">Commands run inside the <span class="mono"><?= e($project) ?></span> folder. <kbd>↑</kbd> <kbd>↓</kbd> history · <kbd>Ctrl</kbd>+<kbd>L</kbd> clear</div>
            </div>
        </div>

        <div class="chips">
            <?php foreach ($quick as [$ic, $cmd]): ?>
                <button class="chip" type="button" data-cmd="<?= e($cmd) ?>"><?= icon($ic, 14) ?> <?= e($cmd) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="term console" id="term">
            <div class="term-bar">
                <div class="term-dots"><i></i><i></i><i></i></div>
                <div class="term-title"><?= e($project) ?> — <?= isWindows() ? 'cmd' : 'sh' ?></div>
                <span class="spinner" title="Running…"></span>
                <button class="icon-btn" type="button" id="term-clear" title="Clear"><?= icon('trash', 15) ?></button>
            </div>
            <div class="term-body" id="term-body">
                <div class="term-welcome">Python Bot Panel terminal · <b><?= e(PHP_OS) ?></b> · python: <b><?= e($py) ?></b>
Type <b>help</b> to see useful commands.</div>
                <div id="term-output"></div>
                <form id="term-form" class="term-input" autocomplete="off">
                    <span class="prompt"><?= e($project) ?><i> $</i></span>
                    <input id="term-command" type="text" spellcheck="false" autofocus aria-label="Command">
                </form>
            </div>
        </div>

<script>
(function () {
    var PROJECT = <?= json_encode($project) ?>;
    var CSRF = <?= json_encode(csrfToken()) ?>;
    var term = document.getElementById('term');
    var body = document.getElementById('term-body');
    var out = document.getElementById('term-output');
    var form = document.getElementById('term-form');
    var input = document.getElementById('term-command');
    var key = 'bp-history-' + PROJECT;
    var history = [];
    try { history = JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) {}
    var hIndex = history.length;
    var busy = false;

    function scroll() { body.scrollTop = body.scrollHeight; }
    function clearScreen() { out.innerHTML = ''; }

    function remember(cmd) {
        if (history[history.length - 1] !== cmd) history.push(cmd);
        history = history.slice(-100);
        hIndex = history.length;
        try { localStorage.setItem(key, JSON.stringify(history)); } catch (e) {}
    }

    async function run(cmd) {
        if (busy) return;
        if (cmd === 'clear' || cmd === 'cls') { clearScreen(); remember(cmd); return; }
        remember(cmd);

        var line = document.createElement('div');
        line.className = 'term-cmd';
        line.innerHTML = '<span class="prompt"></span> ';
        line.firstChild.textContent = PROJECT + ' $';
        line.appendChild(document.createTextNode(cmd));
        out.appendChild(line);

        var result = document.createElement('pre');
        out.appendChild(result);
        busy = true;
        term.classList.add('term-running');
        input.disabled = true;
        scroll();

        var data = new FormData();
        data.append('project', PROJECT);
        data.append('command', cmd);
        data.append('csrf_token', CSRF);

        try {
            var res = await fetch('api_terminal.php', { method: 'POST', body: data, cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            var text = '';
            if (res.body && res.body.getReader) {
                var reader = res.body.getReader();
                var dec = new TextDecoder();
                while (true) {
                    var part = await reader.read();
                    if (part.done) break;
                    text += dec.decode(part.value, { stream: true });
                    result.textContent = text;
                    scroll();
                }
                text += dec.decode();
            } else {
                text = await res.text();
            }
            // Pull the "[Exit Code: N]" footer out into its own styled line.
            var m = text.match(/\n*\[Exit Code: (-?\d+)\]\s*$/);
            if (m) {
                text = text.slice(0, m.index);
                var exit = document.createElement('div');
                exit.className = 'term-exit' + (m[1] !== '0' ? ' fail' : '');
                exit.textContent = (m[1] === '0' ? '✓' : '✗') + ' exit code ' + m[1];
                out.appendChild(exit);
            }
            result.textContent = text;
            if (!text) result.remove();
        } catch (err) {
            result.textContent += '\n[Error] ' + err;
        }

        busy = false;
        term.classList.remove('term-running');
        input.disabled = false;
        input.focus();
        scroll();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var cmd = input.value.trim();
        if (!cmd) return;
        input.value = '';
        run(cmd);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowUp') {
            if (hIndex > 0) { hIndex--; input.value = history[hIndex]; }
            e.preventDefault();
        } else if (e.key === 'ArrowDown') {
            if (hIndex < history.length - 1) { hIndex++; input.value = history[hIndex]; }
            else { hIndex = history.length; input.value = ''; }
            e.preventDefault();
        } else if (e.key.toLowerCase() === 'l' && e.ctrlKey) {
            e.preventDefault();
            clearScreen();
        }
    });

    body.addEventListener('click', function () {
        if (!window.getSelection().toString()) input.focus();
    });
    document.getElementById('term-clear').addEventListener('click', clearScreen);
    document.querySelectorAll('[data-cmd]').forEach(function (chip) {
        chip.addEventListener('click', function () { run(chip.getAttribute('data-cmd')); });
    });
})();
</script>
<?php renderFooter(); ?>
