# Python Bot Panel

A lightweight PHP web panel for hosting and managing Python bots and scripts (Telegram, Discord, Bale, or any long-running Python script) on **shared cPanel hosts that don't provide SSH / terminal access**.

Upload the panel to your host, open it in the browser, and you get a file manager, bot process control, live logs and a web terminal — no SSH needed.

## Features

- Multiple bot projects
- File manager: upload (ZIP auto-extract), edit, rename, delete, download
- Start / stop / restart bots in the background (Linux & Windows)
- Live log viewer
- Web terminal with streamed output (`pip install`, `python`, `ls`, ...)

## Requirements

- PHP 8.0+ with the `zip` extension
- `proc_open`, `exec`, `shell_exec` and `popen` must not be in the host's `disable_functions`
- Python 3 available on the server (`python3` on Linux, `python` on Windows — change `PYTHON_COMMAND` in `config.php` if needed)

## Installation (cPanel)

1. Download this repository as ZIP.
2. In cPanel **File Manager**, upload it to a folder (e.g. `public_html/panel`) and extract.
3. Open `https://your-domain.com/panel/` in the browser.
4. Create a bot, upload your `.py` files (or a ZIP), install dependencies from the Terminal page (`pip install -r requirements.txt --user`), then press **Start Bot**.

Runtime data (projects, logs, PID files) is stored in `storage/`, which is created automatically and ignored by git.

## Run locally

```bash
php -S 127.0.0.1:8000
```

## ⚠️ Security notice

This panel is meant for **personal use**. It has **no login**, and the web terminal can run **any shell command** on your server with your account's permissions.
Anyone who knows the URL has full control of your hosting account. Protecting it is your responsibility, for example:

- cPanel → **Directory Privacy** (password-protect the panel folder), and/or
- an unguessable folder name, HTTPS, and IP restrictions in `.htaccess`.

Files under `storage/` (including your bots' `.env` / tokens) may be reachable from the web; add a `storage/.htaccess` with `Require all denied` to block it.

Use at your own risk.

## License

MIT
