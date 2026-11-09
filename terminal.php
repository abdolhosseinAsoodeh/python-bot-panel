<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
ensureProject($project);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terminal - <?= htmlspecialchars($project) ?></title>
    <link rel="stylesheet" href="views/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">🤖 Bot Panel</div>
        <a class="menu" href="dashboard.php?project=<?= urlencode($project) ?>">🏠 Dashboard</a>
        <a class="menu" href="files.php?project=<?= urlencode($project) ?>">📁 Files</a>
        <a class="menu active" href="terminal.php?project=<?= urlencode($project) ?>">🖥 Terminal</a>
        <a class="menu" href="logs.php?project=<?= urlencode($project) ?>">📜 Logs</a>
    </aside>

    <main class="content">
        <div class="topbar">
            <h1>🖥 Terminal (<?= htmlspecialchars($project) ?>)</h1>
            <div style="font-size:12px; color:#94a3b8;">
                Safe isolated execution. Supported commands: pip, python, ls, dir, cat, type, ps, env, etc.
            </div>
        </div>

        <div class="terminal" id="terminal-window">
            <div id="terminal-output">Telegram Bot Panel Terminal
Project: <?= htmlspecialchars($project) ?>
OS: <?= PHP_OS ?>
Python: <?= htmlspecialchars(PYTHON_COMMAND) ?>

Type 'help' for available commands.
</div>
            <form id="terminal-form">
                <div class="terminal-line">
                    <span style="color:#10b981; font-weight:bold;"><?= htmlspecialchars($project) ?>&gt;</span>
                    <input id="terminal-command" type="text" autocomplete="off" autofocus placeholder="Type command here...">
                </div>
            </form>
        </div>
    </main>
</div>

<script>
const form = document.getElementById('terminal-form');
const commandInput = document.getElementById('terminal-command');
const output = document.getElementById('terminal-output');
const terminalWindow = document.getElementById('terminal-window');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const command = commandInput.value.trim();
    if (!command) return;

    output.innerHTML += '<div style="color:#38bdf8; margin-top:8px;">&gt; ' + escapeHtml(command) + '</div>';
    commandInput.value = '';

    const data = new FormData();
    data.append('project', <?= json_encode($project) ?>);
    data.append('command', command);
    data.append('csrf_token', <?= json_encode(csrfToken()) ?>);

    // Create an output area immediately; append each streamed chunk as it arrives.
    const result = document.createElement('pre');
    result.style.margin = '4px 0 10px';
    result.style.whiteSpace = 'pre-wrap';
    result.style.overflowWrap = 'anywhere';
    output.appendChild(result);
    terminalWindow.scrollTop = terminalWindow.scrollHeight;

    try {
        const response = await fetch('api_terminal.php', {
            method: 'POST',
            body: data,
            cache: 'no-store'
        });
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        if (!response.body || !response.body.getReader) {
            result.textContent = await response.text();
        } else {
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            while (true) {
                const part = await reader.read();
                if (part.done) break;
                result.textContent += decoder.decode(part.value, { stream: true });
                terminalWindow.scrollTop = terminalWindow.scrollHeight;
            }
            result.textContent += decoder.decode();
        }
    } catch (error) {
        result.textContent += '\n[Error] ' + error.toString();
    }

    terminalWindow.scrollTop = terminalWindow.scrollHeight;
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
</body>
</html>
