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

// CodeMirror mode per extension: [mode spec, required mode files]
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$modes = [
    'py' => ['python', ['python']],
    'js' => ['javascript', ['javascript']],
    'ts' => ['text/typescript', ['javascript']],
    'json' => ['application/json', ['javascript']],
    'php' => ['application/x-httpd-php', ['xml', 'javascript', 'css', 'htmlmixed', 'clike', 'php']],
    'html' => ['htmlmixed', ['xml', 'javascript', 'css', 'htmlmixed']],
    'htm' => ['htmlmixed', ['xml', 'javascript', 'css', 'htmlmixed']],
    'css' => ['css', ['css']],
    'scss' => ['text/x-scss', ['css']],
    'xml' => ['xml', ['xml']],
    'sh' => ['shell', ['shell']],
    'yaml' => ['yaml', ['yaml']],
    'yml' => ['yaml', ['yaml']],
    'md' => ['markdown', ['markdown']],
    'sql' => ['sql', ['sql']],
    'toml' => ['toml', ['toml']],
    'ini' => ['properties', ['properties']],
    'env' => ['properties', ['properties']],
    'cfg' => ['properties', ['properties']],
    'conf' => ['properties', ['properties']],
];
[$mode, $modeFiles] = $modes[$ext] ?? ['null', []];
$cm = 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16';

$dir = dirname($file) === '.' ? '' : dirname($file);
$backUrl = 'files.php?project=' . urlencode($project) . '&path=' . urlencode($dir);

renderHeader(basename($path), 'files', $project);
?>
        <link rel="stylesheet" href="<?= $cm ?>/codemirror.min.css">

        <div class="page-header">
            <div>
                <h1 class="page-title">Edit file</h1>
                <div class="page-sub mono"><?= e($project) ?>/<?= e($file) ?></div>
            </div>
            <div class="page-actions">
                <a class="btn btn-ghost" href="<?= e($backUrl) ?>"><?= icon('arrow-left', 16) ?> Back</a>
                <a class="btn" href="download.php?project=<?= urlencode($project) ?>&file=<?= urlencode($file) ?>"><?= icon('download', 16) ?> Download</a>
                <button class="btn btn-primary" type="submit" form="editor-form"><?= icon('save', 16) ?> Save</button>
            </div>
        </div>

        <form method="post" id="editor-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="editor-shell" id="editor-shell">
                <div class="editor-bar">
                    <span class="file-tab"><?= fileIcon(basename($path), false) ?> <?= e(basename($path)) ?> <span class="dirty-dot" title="Unsaved changes"></span></span>
                    <span><span id="cursor-pos">Ln 1, Col 1</span> · <?= e(strtoupper($ext)) ?> · <kbd>Ctrl</kbd>+<kbd>S</kbd> to save</span>
                </div>
                <textarea name="content" id="code" class="code-editor" spellcheck="false"><?= e($content) ?></textarea>
            </div>
        </form>

<script src="<?= $cm ?>/codemirror.min.js"></script>
<?php foreach ($modeFiles as $m): ?>
<script src="<?= $cm ?>/mode/<?= $m ?>/<?= $m ?>.min.js"></script>
<?php endforeach; ?>
<script src="<?= $cm ?>/addon/selection/active-line.min.js"></script>
<script src="<?= $cm ?>/addon/edit/matchbrackets.min.js"></script>
<script src="<?= $cm ?>/addon/edit/closebrackets.min.js"></script>
<script>
(function () {
    var form = document.getElementById('editor-form');
    var shell = document.getElementById('editor-shell');
    var textarea = document.getElementById('code');
    var pos = document.getElementById('cursor-pos');
    var dirty = false;
    var editor = null;

    function setDirty(v) { dirty = v; shell.classList.toggle('dirty', v); }

    if (window.CodeMirror) {
        editor = CodeMirror.fromTextArea(textarea, {
            mode: <?= json_encode($mode) ?>,
            lineNumbers: true,
            indentUnit: 4,
            tabSize: 4,
            indentWithTabs: false,
            styleActiveLine: true,
            matchBrackets: true,
            autoCloseBrackets: true,
            lineWrapping: false,
            extraKeys: {
                Tab: function (cm) {
                    if (cm.somethingSelected()) cm.indentSelection('add');
                    else cm.replaceSelection(Array(cm.getOption('indentUnit') + 1).join(' '), 'end');
                },
                'Shift-Tab': function (cm) { cm.indentSelection('subtract'); }
            }
        });
        editor.on('change', function () { setDirty(true); });
        editor.on('cursorActivity', function () {
            var c = editor.getCursor();
            pos.textContent = 'Ln ' + (c.line + 1) + ', Col ' + (c.ch + 1);
        });
        editor.focus();
    } else {
        textarea.addEventListener('input', function () { setDirty(true); });
    }

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            if (editor) editor.save();
            setDirty(false);
            form.submit();
        }
    });
    form.addEventListener('submit', function () { setDirty(false); });
    window.addEventListener('beforeunload', function (e) {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    <?php if ($saved): ?>document.addEventListener('DOMContentLoaded', function () { toast('File saved'); });<?php endif; ?>
})();
</script>
<?php renderFooter(); ?>
