(function () {
    var root = document.documentElement;

    // Theme toggle (dark / light), remembered per browser.
    var themeBtn = document.getElementById('theme-toggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            var current = root.getAttribute('data-theme');
            if (!current) {
                current = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            }
            var next = current === 'light' ? 'dark' : 'light';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('bp-theme', next); } catch (e) {}
        });
    }

    // Mobile sidebar
    document.querySelectorAll('[data-sidebar-toggle]').forEach(function (el) {
        el.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-open');
        });
    });

    // Dialogs
    document.querySelectorAll('[data-dialog-open]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            ev.preventDefault();
            var dlg = document.getElementById(el.getAttribute('data-dialog-open'));
            if (dlg && dlg.showModal) {
                document.body.classList.remove('sidebar-open');
                dlg.showModal();
            }
        });
    });
    document.querySelectorAll('dialog').forEach(function (dlg) {
        dlg.addEventListener('click', function (ev) {
            if (ev.target === dlg) dlg.close();
        });
        dlg.querySelectorAll('[data-dialog-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { dlg.close(); });
        });
    });

    // Toasts
    window.toast = function (message, type) {
        var box = document.getElementById('toasts');
        if (!box) return;
        var t = document.createElement('div');
        t.className = 'toast' + (type === 'error' ? ' error' : '');
        var ok = '<path d="M20 6 9 17l-5-5"/>';
        var bad = '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>';
        t.innerHTML = '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">' + (type === 'error' ? bad : ok) + '</svg>';
        var span = document.createElement('span');
        span.textContent = message;
        t.appendChild(span);
        box.appendChild(t);
        setTimeout(function () {
            t.classList.add('leaving');
            setTimeout(function () { t.remove(); }, 250);
        }, 2800);
    };

    // Confirm dialog for destructive links: <a data-confirm="...">
    var confirmDlg = document.getElementById('confirm-dialog');
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            if (!confirmDlg || !confirmDlg.showModal) {
                if (!window.confirm(el.getAttribute('data-confirm'))) ev.preventDefault();
                return;
            }
            ev.preventDefault();
            confirmDlg.querySelector('[data-confirm-text]').textContent = el.getAttribute('data-confirm');
            confirmDlg.querySelector('[data-confirm-go]').setAttribute('href', el.getAttribute('href'));
            confirmDlg.showModal();
        });
    });
})();
