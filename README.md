# Python Bot Panel

[English](#english) | [فارسی](#فارسی)

---

## English

A lightweight PHP web panel for hosting and managing Python bots and scripts (Telegram, Discord, Bale, or any long-running Python script) on **shared cPanel hosts that don't provide SSH / terminal access**.

Upload the panel to your host, open it in the browser, and you get a file manager, process control, live logs and a web terminal. You don't need SSH.

### Features

- **Multiple bots:** each bot gets its own folder, log and process
- **Dashboard:** live status, PID, uptime, file/disk stats, recent output
- **File manager:** drag & drop upload with progress, ZIP auto-extract, rename, delete, download, new folder
- **Code editor:** syntax highlighting (Python, JS, PHP, YAML, ...), line numbers, `Ctrl+S` to save
- **Bot control:** start / stop / restart in the background, choose which script to run
- **Live logs:** auto-refresh, line filter, error/warning highlighting, line numbers
- **Web terminal:** streamed output, command history, quick commands (`pip install -r requirements.txt`, ...)
- **Dark / light theme** and a mobile-friendly layout

### Requirements

- PHP 8.0+ with the `zip` extension
- `proc_open`, `exec`, `shell_exec` and `popen` must **not** be listed in the host's `disable_functions`
- Python 3 on the server (`python3` on Linux, `python` on Windows; change `PYTHON_COMMAND` in `config.php` if needed)

### Installation (cPanel)

1. Download this repository as a ZIP (**Code → Download ZIP**).
2. In cPanel **File Manager**, upload it to a folder (e.g. `public_html/panel`) and extract it.
3. Open `https://your-domain.com/panel/` in your browser.
4. Create a bot, upload your `.py` files (or a ZIP), and install dependencies from the **Terminal** page:
   ```
   python3 -m pip install -r requirements.txt --user
   ```
5. Press **Start bot** on the dashboard.

Runtime data (bots, logs, PID files) is stored in `storage/`. It is created automatically and ignored by git.

### Run locally

```bash
php -S 127.0.0.1:8000
```

Then open <http://127.0.0.1:8000>.

### ⚠️ Security notice

This panel is meant for **personal use**. It has **no login**, and the web terminal can run **any shell command** on your server with your account's permissions.
**Anyone who knows the URL has full control of your hosting account.** Protecting it is your responsibility. For example:

- Password-protect the panel folder with cPanel → **Directory Privacy**
- Use an unguessable folder name, HTTPS, and IP restrictions in `.htaccess`
- Block web access to `storage/` (it contains your bots' `.env` files and tokens) by adding `storage/.htaccess` with:
  ```
  Require all denied
  ```

Use at your own risk.

---

<div dir="rtl">

## فارسی

یک پنل تحت وب سبک با PHP برای میزبانی و مدیریت ربات‌ها و اسکریپت‌های پایتون (تلگرام، دیسکورد، بله یا هر اسکریپت پایتونی که دائم در حال اجراست). این پنل برای **هاست‌های اشتراکی cPanel که دسترسی SSH یا ترمینال ندارند** ساخته شده است.

پنل را روی هاست آپلود کنید و در مرورگر باز کنید. مدیریت فایل، کنترل اجرای ربات، لاگ زنده و ترمینال تحت وب در اختیارتان قرار می‌گیرد و دیگر به SSH نیازی ندارید.

### امکانات

- **چند ربات همزمان:** هر ربات پوشه، لاگ و پروسس جداگانه دارد
- **داشبورد:** وضعیت زنده، PID، مدت اجرا، آمار فایل‌ها و حجم، آخرین خروجی
- **مدیریت فایل:** آپلود با کشیدن و رها کردن (Drag & Drop) همراه با نوار پیشرفت، استخراج خودکار ZIP، تغییر نام، حذف، دانلود و ساخت پوشه
- **ویرایشگر کد:** رنگ‌بندی کد (پایتون، JS، PHP، YAML و …)، شماره‌ی خط، ذخیره با `Ctrl+S`
- **کنترل ربات:** روشن، خاموش و راه‌اندازی مجدد در پس‌زمینه، با امکان انتخاب اسکریپت اجرایی
- **لاگ زنده:** به‌روزرسانی خودکار، جستجو بین خط‌ها، رنگی شدن خطاها و هشدارها
- **ترمینال تحت وب:** نمایش زنده‌ی خروجی، تاریخچه‌ی دستورها، دستورهای آماده (مثل `pip install -r requirements.txt`)
- **تم تاریک و روشن** و سازگار با موبایل

### پیش‌نیازها

- PHP نسخه‌ی 8.0 یا بالاتر با افزونه‌ی `zip`
- توابع `proc_open`، `exec`، `shell_exec` و `popen` نباید در `disable_functions` هاست غیرفعال شده باشند
- پایتون ۳ روی سرور (در لینوکس `python3` و در ویندوز `python`؛ در صورت نیاز مقدار `PYTHON_COMMAND` را در فایل `config.php` تغییر دهید)

### نصب روی cPanel

۱. این مخزن را به صورت ZIP دانلود کنید (**Code ← Download ZIP**).

۲. در **File Manager** سی‌پنل، فایل را در یک پوشه (مثلاً `public_html/panel`) آپلود و استخراج کنید.

۳. آدرس `https://your-domain.com/panel/` را در مرورگر باز کنید.

۴. یک ربات بسازید، فایل‌های `.py` (یا یک فایل ZIP) را آپلود کنید و از صفحه‌ی **Terminal** پیش‌نیازها را نصب کنید:

<div dir="ltr">

```
python3 -m pip install -r requirements.txt --user
```

</div>

۵. در داشبورد روی **Start bot** بزنید.

اطلاعات اجرایی (ربات‌ها، لاگ‌ها و فایل‌های PID) در پوشه‌ی `storage/` ذخیره می‌شوند. این پوشه خودکار ساخته می‌شود و وارد گیت نمی‌شود.

### اجرا روی سیستم خودتان

<div dir="ltr">

```bash
php -S 127.0.0.1:8000
```

</div>

سپس آدرس <http://127.0.0.1:8000> را باز کنید.

### ⚠️ هشدار امنیتی

این پنل برای **استفاده‌ی شخصی** ساخته شده است. پنل **صفحه‌ی ورود و رمز ندارد** و ترمینال آن می‌تواند **هر دستوری** را با دسترسی حساب هاست شما اجرا کند.
**هر کسی آدرس پنل را بداند، کنترل کامل هاست شما را در دست دارد.** محافظت از آن به عهده‌ی خودتان است. برای مثال:

- با گزینه‌ی **Directory Privacy** در سی‌پنل برای پوشه‌ی پنل رمز بگذارید
- از یک نام پوشه‌ی غیرقابل حدس، HTTPS و محدودیت IP در `.htaccess` استفاده کنید
- دسترسی وب به پوشه‌ی `storage/` را ببندید (فایل‌های `.env` و توکن ربات‌ها آنجاست). برای این کار فایل `storage/.htaccess` را با این محتوا بسازید:

<div dir="ltr">

```
Require all denied
```

</div>

مسئولیت استفاده از این پنل با خود شماست.

</div>

---

## License

MIT
