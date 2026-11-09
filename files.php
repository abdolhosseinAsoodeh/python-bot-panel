<?php
require_once __DIR__ . '/functions.php';

$project = projectName();
$relative = isset($_GET['path']) ? trim($_GET['path'], '/') : '';

try {
    $directory = safePath($project, $relative);
} catch (Exception $e) {
    die('Directory not found or invalid.');
}

if (!is_dir($directory)) {
    die('Directory not found.');
}

$items = scandir($directory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Files - <?= htmlspecialchars($project) ?></title>
    <link rel="stylesheet" href="views/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">🤖 Bot Panel</div>
        <a href="dashboard.php?project=<?= urlencode($project) ?>" class="menu">🏠 Dashboard</a>
        <a href="files.php?project=<?= urlencode($project) ?>" class="menu active">📁 Files</a>
        <a href="terminal.php?project=<?= urlencode($project) ?>" class="menu">🖥 Terminal</a>
        <a href="logs.php?project=<?= urlencode($project) ?>" class="menu">📜 Logs</a>
    </aside>

    <main class="content">
        <div class="topbar">
            <h1>📁 File Manager (<?= htmlspecialchars($project) ?>)</h1>
            <a class="btn" href="dashboard.php?project=<?= urlencode($project) ?>">← Back to Dashboard</a>
        </div>

        <div class="panel">
            <div class="toolbar">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="project" value="<?= htmlspecialchars($project) ?>">
                    <input type="hidden" name="path" value="<?= htmlspecialchars($relative) ?>">
                    <input type="file" name="files[]" multiple required>
                    <button class="btn success" type="submit">⬆ Upload (ZIP auto-extracts)</button>
                </form>

                <form action="mkdir.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="project" value="<?= htmlspecialchars($project) ?>">
                    <input type="hidden" name="path" value="<?= htmlspecialchars($relative) ?>">
                    <input type="text" name="name" placeholder="Folder name" required>
                    <button class="btn" type="submit">📁 New Folder</button>
                </form>
            </div>

            <div class="breadcrumb">
                📁 <a href="files.php?project=<?= urlencode($project) ?>"><?= htmlspecialchars($project) ?></a>
                <?php
                if ($relative) {
                    $parts = explode('/', $relative);
                    $accum = '';
                    foreach ($parts as $p) {
                        $accum .= ($accum ? '/' : '') . $p;
                        echo ' / <a href="files.php?project=' . urlencode($project) . '&path=' . urlencode($accum) . '">' . htmlspecialchars($p) . '</a>';
                    }
                }
                ?>
            </div>

            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Modified</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($relative): ?>
                    <tr>
                        <td colspan="5">
                            <a href="files.php?project=<?= urlencode($project) ?>&path=<?= urlencode(dirname($relative) === '.' ? '' : dirname($relative)) ?>">
                                ⬅ .. Up one level
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
                    $itemRelative = $relative ? $relative . '/' . $item : $item;
                    $isDir = is_dir($fullPath);
                    ?>
                    <tr>
                        <td>
                            <?php if ($isDir): ?>
                                📁 <a href="files.php?project=<?= urlencode($project) ?>&path=<?= urlencode($itemRelative) ?>">
                                    <strong><?= htmlspecialchars($item) ?></strong>
                                </a>
                            <?php else: ?>
                                📄 <?= htmlspecialchars($item) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $isDir ? 'Folder' : strtoupper(pathinfo($item, PATHINFO_EXTENSION)) ?></td>
                        <td><?= $isDir ? '-' : formatSize((int)filesize($fullPath)) ?></td>
                        <td><?= date('Y-m-d H:i', (int)filemtime($fullPath)) ?></td>
                        <td class="actions-small">
                            <?php if (!$isDir && isTextFile($item)): ?>
                                <a class="btn small" href="editor.php?project=<?= urlencode($project) ?>&file=<?= urlencode($itemRelative) ?>">
                                    ✏️ Edit
                                </a>
                            <?php endif; ?>
                            <?php if (!$isDir): ?>
                                <a class="btn small" href="download.php?project=<?= urlencode($project) ?>&file=<?= urlencode($itemRelative) ?>">
                                    ⬇
                                </a>
                            <?php endif; ?>
                            <a class="btn small" href="#" onclick="promptRename('<?= htmlspecialchars($itemRelative, ENT_QUOTES) ?>', '<?= htmlspecialchars($item, ENT_QUOTES) ?>')">
                                🏷
                            </a>
                            <a class="btn small danger" onclick="return confirm('Delete <?= htmlspecialchars($item, ENT_QUOTES) ?>?')" href="delete.php?project=<?= urlencode($project) ?>&file=<?= urlencode($itemRelative) ?>&csrf_token=<?= csrfToken() ?>">
                                🗑
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<form id="renameForm" action="rename.php" method="post" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="project" value="<?= htmlspecialchars($project) ?>">
    <input type="hidden" name="file" id="renameFile">
    <input type="hidden" name="name" id="renameName">
</form>

<script>
function promptRename(filePath, currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        document.getElementById('renameFile').value = filePath;
        document.getElementById('renameName').value = newName;
        document.getElementById('renameForm').submit();
    }
}
</script>
</body>
</html>
