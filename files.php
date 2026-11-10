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

// Folders first, then files, both alphabetical.
$entries = [];
foreach (scandir($directory) as $item) {
    if ($item === '.' || $item === '..') {
        continue;
    }
    $full = $directory . DIRECTORY_SEPARATOR . $item;
    $entries[] = [
        'name' => $item,
        'rel' => $relative ? $relative . '/' . $item : $item,
        'dir' => is_dir($full),
        'size' => is_file($full) ? (int)filesize($full) : 0,
        'mtime' => (int)filemtime($full),
    ];
}
usort($entries, function ($a, $b) {
    if ($a['dir'] !== $b['dir']) {
        return $a['dir'] ? -1 : 1;
    }
    return strnatcasecmp($a['name'], $b['name']);
});

$q = 'project=' . urlencode($project);
$csrf = csrfToken();

renderHeader('Files', 'files', $project);
?>
        <div class="page-header">
            <div>
                <h1 class="page-title">File manager</h1>
                <nav class="crumbs page-sub" aria-label="Breadcrumb">
                    <a href="files.php?<?= $q ?>"><?= e($project) ?></a>
                    <?php
                    if ($relative) {
                        $accum = '';
                        foreach (explode('/', $relative) as $p) {
                            $accum .= ($accum ? '/' : '') . $p;
                            echo icon('chevron-right', 14) . '<a href="files.php?' . $q . '&path=' . urlencode($accum) . '">' . e($p) . '</a>';
                        }
                    }
                    ?>
                </nav>
            </div>
            <div class="page-actions">
                <button class="btn" type="button" data-dialog-open="mkdir-dialog"><?= icon('folder-plus', 16) ?> New folder</button>
                <button class="btn btn-primary" type="button" id="upload-btn"><?= icon('upload', 16) ?> Upload</button>
            </div>
        </div>

        <form id="upload-form" action="upload.php" method="post" enctype="multipart/form-data" hidden>
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="project" value="<?= e($project) ?>">
            <input type="hidden" name="path" value="<?= e($relative) ?>">
            <input type="file" name="files[]" id="upload-input" multiple>
        </form>

        <section class="panel dropzone" id="dropzone">
            <div class="dropzone-overlay"><?= icon('upload', 32) ?> Drop files to upload<small class="muted">ZIP files are extracted automatically</small></div>
            <div class="upload-progress" id="upload-progress"></div>

            <?php if (empty($entries) && !$relative): ?>
                <div class="empty">
                    <div class="empty-icon"><?= icon('upload', 26) ?></div>
                    <h3>This bot has no files yet</h3>
                    <p>Drag &amp; drop your bot files here, or upload a ZIP — it will be extracted automatically.</p>
                    <button class="btn btn-primary" type="button" onclick="document.getElementById('upload-input').click()"><?= icon('upload', 16) ?> Choose files</button>
                </div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th class="col-name">Name</th>
                        <th class="col-hide-sm">Size</th>
                        <th class="col-hide-sm">Modified</th>
                        <th class="col-actions"><span class="sr-only">Actions</span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($relative): ?>
                        <tr>
                            <td colspan="4">
                                <a class="fname" href="files.php?<?= $q ?>&path=<?= urlencode(dirname($relative) === '.' ? '' : dirname($relative)) ?>">
                                    <span class="ficon"><?= icon('level-up', 16) ?></span><span class="muted">Back to parent folder</span>
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if (empty($entries)): ?>
                        <tr><td colspan="4" class="muted" style="text-align:center; padding:36px">This folder is empty — drop files here to upload.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($entries as $it):
                        $editable = !$it['dir'] && isTextFile($it['name']);
                        $href = $it['dir']
                            ? 'files.php?' . $q . '&path=' . urlencode($it['rel'])
                            : ($editable ? 'editor.php?' . $q . '&file=' . urlencode($it['rel']) : 'download.php?' . $q . '&file=' . urlencode($it['rel']));
                    ?>
                        <tr>
                            <td class="col-name">
                                <a class="fname" href="<?= e($href) ?>" title="<?= e($it['name']) ?>">
                                    <?= fileIcon($it['name'], $it['dir']) ?><span><?= e($it['name']) ?></span>
                                </a>
                            </td>
                            <td class="muted col-hide-sm"><?= $it['dir'] ? '—' : e(formatSize($it['size'])) ?></td>
                            <td class="muted col-hide-sm" title="<?= e(date('Y-m-d H:i', $it['mtime'])) ?>"><?= e(timeAgo($it['mtime'])) ?></td>
                            <td class="col-actions">
                                <div class="row-actions">
                                    <?php if ($editable): ?>
                                        <a class="icon-btn" title="Edit" href="editor.php?<?= $q ?>&file=<?= urlencode($it['rel']) ?>"><?= icon('pencil', 16) ?></a>
                                    <?php endif; ?>
                                    <?php if (!$it['dir']): ?>
                                        <a class="icon-btn" title="Download" href="download.php?<?= $q ?>&file=<?= urlencode($it['rel']) ?>"><?= icon('download', 16) ?></a>
                                    <?php endif; ?>
                                    <button class="icon-btn" type="button" title="Rename" data-rename="<?= e($it['rel']) ?>" data-name="<?= e($it['name']) ?>"><?= icon('rename', 16) ?></button>
                                    <a class="icon-btn danger" title="Delete"
                                       href="delete.php?<?= $q ?>&file=<?= urlencode($it['rel']) ?>&csrf_token=<?= urlencode($csrf) ?>"
                                       data-confirm="“<?= e($it['name']) ?>”<?= $it['dir'] ? ' and everything inside it' : '' ?> will be permanently deleted."><?= icon('trash', 16) ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

<dialog class="dialog" id="mkdir-dialog">
    <form action="mkdir.php" method="post">
        <div class="dialog-head">
            <div class="dialog-icon"><?= icon('folder-plus', 20) ?></div>
            <div><h3>New folder</h3><p>Create a folder in /<?= e($relative) ?></p></div>
        </div>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="project" value="<?= e($project) ?>">
        <input type="hidden" name="path" value="<?= e($relative) ?>">
        <label class="field"><span>Folder name</span><input type="text" name="name" required autofocus></label>
        <div class="dialog-actions">
            <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
            <button class="btn btn-primary" type="submit">Create folder</button>
        </div>
    </form>
</dialog>

<dialog class="dialog" id="rename-dialog">
    <form action="rename.php" method="post">
        <div class="dialog-head">
            <div class="dialog-icon"><?= icon('rename', 20) ?></div>
            <div><h3>Rename</h3><p id="rename-from"></p></div>
        </div>
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="project" value="<?= e($project) ?>">
        <input type="hidden" name="file" id="rename-file">
        <label class="field"><span>New name</span><input type="text" name="name" id="rename-name" required></label>
        <div class="dialog-actions">
            <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
            <button class="btn btn-primary" type="submit">Rename</button>
        </div>
    </form>
</dialog>

<script>
(function () {
    var input = document.getElementById('upload-input');
    var form = document.getElementById('upload-form');
    var zone = document.getElementById('dropzone');
    var bar = document.getElementById('upload-progress');

    function upload(files) {
        if (!files || !files.length) return;
        var data = new FormData(form);
        data.delete('files[]');
        for (var i = 0; i < files.length; i++) data.append('files[]', files[i]);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
        xhr.upload.onprogress = function (ev) {
            if (ev.lengthComputable) bar.style.width = (ev.loaded / ev.total * 100) + '%';
        };
        xhr.onload = function () { location.reload(); };
        xhr.onerror = function () { bar.style.width = 0; toast('Upload failed', 'error'); };
        xhr.send(data);
    }

    document.getElementById('upload-btn').addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () { upload(input.files); });

    var depth = 0;
    zone.addEventListener('dragenter', function (e) { e.preventDefault(); depth++; zone.classList.add('dragging'); });
    zone.addEventListener('dragover', function (e) { e.preventDefault(); });
    zone.addEventListener('dragleave', function () { if (--depth <= 0) { depth = 0; zone.classList.remove('dragging'); } });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        depth = 0;
        zone.classList.remove('dragging');
        upload(e.dataTransfer.files);
    });

    var dlg = document.getElementById('rename-dialog');
    document.querySelectorAll('[data-rename]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name = btn.getAttribute('data-name');
            document.getElementById('rename-file').value = btn.getAttribute('data-rename');
            document.getElementById('rename-from').textContent = 'Current name: ' + name;
            var field = document.getElementById('rename-name');
            field.value = name;
            dlg.showModal();
            var dot = name.lastIndexOf('.');
            field.setSelectionRange(0, dot > 0 ? dot : name.length);
        });
    });
})();
</script>
<?php renderFooter(); ?>
