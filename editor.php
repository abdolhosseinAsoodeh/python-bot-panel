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

if (!isTextFile($path)) {
    die('This file type cannot be edited in the browser.');
}

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $content = $_POST['content'] ?? '';
    file_put_contents($path, $content);
    $saved = true;
} else {
    $content = file_get_contents($path);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit <?= htmlspecialchars(basename($path)) ?></title>
    <link rel="stylesheet" href="views/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">🤖 Bot Panel</div>
        <a class="menu" href="dashboard.php?project=<?= urlencode($project) ?>">🏠 Dashboard</a>
        <a class="menu active" href="files.php?project=<?= urlencode($project) ?>">📁 Files</a>
        <a class="menu" href="terminal.php?project=<?= urlencode($project) ?>">🖥 Terminal</a>
        <a class="menu" href="logs.php?project=<?= urlencode($project) ?>">📜 Logs</a>
    </aside>

    <main class="content">
        <div class="topbar">
            <h1>✏️ <?= htmlspecialchars($file) ?></h1>
            <a class="btn" href="files.php?project=<?= urlencode($project) ?>&path=<?= urlencode(dirname($file) === '.' ? '' : dirname($file)) ?>">
                ← Back to Files
            </a>
        </div>

        <?php if ($saved): ?>
            <div class="alert success-alert">
                File saved successfully.
            </div>
        <?php endif; ?>

        <div class="editor-panel">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <textarea name="content" class="code-editor" spellcheck="false"><?= htmlspecialchars($content) ?></textarea>
                <div style="margin-top:15px; display:flex; gap:10px;">
                    <button class="btn success" type="submit">💾 Save File</button>
                    <a class="btn" href="files.php?project=<?= urlencode($project) ?>">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
