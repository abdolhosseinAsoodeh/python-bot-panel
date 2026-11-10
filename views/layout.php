<?php
// Shared layout: icons, page shell (sidebar + header) and footer.

const APP_NAME = 'Bot Panel';
const APP_VERSION = '1.1.0';

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function icon(string $name, int $size = 18, string $class = ''): string
{
    static $icons = [
        'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        'folder' => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
        'folder-plus' => '<path d="M12 10v6"/><path d="M9 13h6"/><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
        'terminal' => '<polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/>',
        'logs' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
        'play' => '<polygon points="6 3 20 12 6 21 6 3"/>',
        'stop' => '<rect width="14" height="14" x="5" y="5" rx="2"/>',
        'restart' => '<path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>',
        'refresh' => '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>',
        'file' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>',
        'file-code' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m10 13-2 2 2 2"/><path d="m14 17 2-2-2-2"/>',
        'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
        'archive' => '<rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>',
        'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
        'pencil' => '<path d="M21.17 6.81a1 1 0 0 0-3.99-3.99L3.84 16.17a2 2 0 0 0-.5.83l-1.32 4.35a.5.5 0 0 0 .62.62l4.35-1.32a2 2 0 0 0 .83-.5z"/><path d="m15 5 4 4"/>',
        'rename' => '<polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/>',
        'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'arrow-down' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'level-up' => '<polyline points="14 9 9 4 4 9"/><path d="M20 20h-7a4 4 0 0 1-4-4V4"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
        'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        'bot' => '<path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>',
        'save' => '<path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/>',
        'activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'menu' => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'package' => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"/>',
        'hard-drive' => '<line x1="22" x2="2" y1="12" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" x2="6.01" y1="16" y2="16"/><line x1="10" x2="10.01" y1="16" y2="16"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'alert' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'external' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
    ];
    $paths = $icons[$name] ?? $icons['file'];
    return '<svg class="icon ' . e($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

function fileIcon(string $name, bool $isDir): string
{
    if ($isDir) {
        return '<span class="ficon ficon-folder">' . icon('folder', 18) . '</span>';
    }
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $map = [
        'py' => ['file-code', 'py'], 'php' => ['file-code', 'php'], 'js' => ['file-code', 'js'], 'ts' => ['file-code', 'js'],
        'html' => ['file-code', 'web'], 'htm' => ['file-code', 'web'], 'css' => ['file-code', 'web'], 'sh' => ['file-code', 'sh'],
        'json' => ['file-text', 'data'], 'yaml' => ['file-text', 'data'], 'yml' => ['file-text', 'data'], 'toml' => ['file-text', 'data'],
        'ini' => ['file-text', 'data'], 'env' => ['file-text', 'data'], 'cfg' => ['file-text', 'data'], 'xml' => ['file-text', 'data'],
        'txt' => ['file-text', 'txt'], 'md' => ['file-text', 'txt'], 'log' => ['file-text', 'txt'], 'csv' => ['file-text', 'data'],
        'zip' => ['archive', 'zip'], 'rar' => ['archive', 'zip'], 'gz' => ['archive', 'zip'], 'tar' => ['archive', 'zip'], '7z' => ['archive', 'zip'],
        'png' => ['image', 'img'], 'jpg' => ['image', 'img'], 'jpeg' => ['image', 'img'], 'gif' => ['image', 'img'], 'webp' => ['image', 'img'], 'svg' => ['image', 'img'],
    ];
    [$ic, $cls] = $map[$ext] ?? ['file', 'other'];
    return '<span class="ficon ficon-' . $cls . '">' . icon($ic, 18) . '</span>';
}

function timeAgo(int $timestamp): string
{
    $diff = time() - $timestamp;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 86400 * 30) return floor($diff / 86400) . 'd ago';
    return date('Y-m-d', $timestamp);
}

function renderHeader(string $title, string $active, string $project): void
{
    $projects = getAllProjects();
    $nav = [
        'dashboard' => ['Dashboard', 'dashboard.php'],
        'files' => ['Files', 'files.php'],
        'terminal' => ['Terminal', 'terminal.php'],
        'logs' => ['Logs', 'logs.php'],
    ];
    $q = '?project=' . urlencode($project);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
    <script>
        try { var t = localStorage.getItem('bp-theme'); if (t) document.documentElement.setAttribute('data-theme', t); } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237c5cff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M12 8V4H8'/><rect width='16' height='12' x='4' y='8' rx='2'/><path d='M15 13v2'/><path d='M9 13v2'/></svg>">
    <link rel="stylesheet" href="views/style.css?v=<?= e(APP_VERSION) ?>">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark"><?= icon('bot', 20) ?></div>
            <div>
                <div class="brand-name"><?= e(APP_NAME) ?></div>
                <div class="brand-sub">Python bot hosting</div>
            </div>
            <button class="icon-btn sidebar-close" type="button" data-sidebar-toggle aria-label="Close menu"><?= icon('x') ?></button>
        </div>

        <div class="side-label">Bot</div>
        <form method="get" action="<?= e($nav[$active][1] ?? 'dashboard.php') ?>" class="project-switch">
            <span class="dot <?= botRunning($project) ? 'dot-on' : 'dot-off' ?>"></span>
            <select name="project" onchange="this.form.submit()" aria-label="Select bot">
                <?php foreach ($projects as $p): ?>
                    <option value="<?= e($p) ?>" <?= $p === $project ? 'selected' : '' ?>><?= e($p) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <button class="btn btn-ghost btn-block new-bot-btn" type="button" data-dialog-open="new-bot-dialog">
            <?= icon('plus', 16) ?> New bot
        </button>

        <div class="side-label">Menu</div>
        <nav class="nav">
            <?php foreach ($nav as $key => [$label, $href]): ?>
                <a class="nav-link <?= $key === $active ? 'active' : '' ?>" href="<?= e($href . $q) ?>">
                    <?= icon($key) ?><span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <button class="theme-toggle" type="button" id="theme-toggle" aria-label="Toggle theme">
                <span class="theme-ic theme-ic-dark"><?= icon('moon', 16) ?></span>
                <span class="theme-ic theme-ic-light"><?= icon('sun', 16) ?></span>
                <span class="theme-label">Theme</span>
            </button>
            <span class="version">v<?= e(APP_VERSION) ?></span>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-toggle></div>

    <main class="content">
        <div class="mobile-bar">
            <button class="icon-btn" type="button" data-sidebar-toggle aria-label="Open menu"><?= icon('menu') ?></button>
            <div class="brand-name"><?= e(APP_NAME) ?></div>
            <span class="mobile-project"><?= e($project) ?></span>
        </div>
<?php
}

function renderFooter(): void
{
    ?>
    </main>
</div>

<dialog class="dialog" id="new-bot-dialog">
    <form method="post" action="mkdir.php">
        <div class="dialog-head">
            <div class="dialog-icon"><?= icon('bot', 20) ?></div>
            <div>
                <h3>Create a new bot</h3>
                <p>Each bot gets its own folder, logs and process.</p>
            </div>
        </div>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="project" value="default">
        <input type="hidden" name="is_project" value="1">
        <label class="field">
            <span>Bot name</span>
            <input type="text" name="name" placeholder="e.g. my-telegram-bot" required autofocus>
        </label>
        <div class="dialog-actions">
            <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
            <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Create</button>
        </div>
    </form>
</dialog>

<dialog class="dialog" id="confirm-dialog">
    <form method="dialog">
        <div class="dialog-head">
            <div class="dialog-icon danger"><?= icon('alert', 20) ?></div>
            <div>
                <h3>Are you sure?</h3>
                <p data-confirm-text></p>
            </div>
        </div>
        <div class="dialog-actions">
            <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
            <a class="btn btn-danger" data-confirm-go href="#">Yes, continue</a>
        </div>
    </form>
</dialog>

<div class="toasts" id="toasts"></div>
<script src="views/app.js?v=<?= e(APP_VERSION) ?>"></script>
</body>
</html>
<?php
}
