# AquaFlow Platform — Production Shared Hosting Deployment & Operations Guide

This guide provides step-by-step instructions for deploying and running the **AquaFlow Water Delivery & Business Management Platform** on any standard Linux cPanel/DirectAdmin/Plesk shared hosting environment.

---

## 1. System Requirements

- **Web Server**: Apache 2.4+ with `mod_rewrite`, `mod_deflate`, `mod_expires`, `mod_headers` enabled.
- **PHP Version**: PHP 8.2 or PHP 8.3 with extensions:
  - `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `PDO_MySQL` / `PDO_MariaDB`, `Tokenizer`, `XML`, `GD` (for PWA icons & image thumbnails).
- **Database**: MariaDB 10.4+ or MySQL 8.0+.
- **Zero Node.js Required**: All frontend assets (Tailwind CSS, Alpine.js, jQuery, Service Worker) are pre-compiled and bundled natively in `public/assets/`. No `npm install`, Node.js daemon, or Vite build step is needed on the server.

---

## 2. Shared Hosting Deployment Architectures

### Option A: Clean Two-Folder Deployment (Recommended for Security)

In this approach, the sensitive core Laravel application code resides outside of the public document root:

```text
/home/cpaneluser/
├── aquaflow_core/              <-- Full Laravel source code (storage, app, database, .env)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   └── .env
└── public_html/                <-- Only the contents of public/
    ├── assets/
    ├── .htaccess
    ├── index.php
    ├── manifest.json
    └── sw.js
```

1. Upload all project files to `/home/cpaneluser/aquaflow_core/`.
2. Move the entire contents of `/home/cpaneluser/aquaflow_core/public/` directly into `/home/cpaneluser/public_html/`.
3. Open `/home/cpaneluser/public_html/index.php` in the cPanel File Manager Code Editor and update the two path lines:

```php
// Change:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// To:
require __DIR__.'/../aquaflow_core/vendor/autoload.php';
$app = require_once __DIR__.'/../aquaflow_core/bootstrap/app.php';
```

---

### Option B: Single-Directory Deployment (Using Root .htaccess)

If your hosting provider restricts files strictly inside `public_html/`:

1. Upload the entire project into `/home/cpaneluser/public_html/`.
2. Ensure the root [`.htaccess`](file:///d:/01.projects/laravel/water_can_ordering/.htaccess) file is in `public_html/`. It automatically:
   - Denies HTTP access to `.env`, `composer.json`, `app/`, `database/`, and `storage/`.
   - Rewrites all web requests to `public/`.

---

## 3. Database Setup (cPanel MySQL / MariaDB)

1. Log into your cPanel account and open **MySQL Database Wizard**.
2. Create a new database: e.g., `cpaneluser_aquaflow`.
3. Create a new database user: e.g., `cpaneluser_aquaflow_user` with a strong password.
4. Assign **ALL PRIVILEGES** to the user on that database.
5. In your `.env` file, configure the connection:

```dotenv
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_aquaflow
DB_USERNAME=cpaneluser_aquaflow_user
DB_PASSWORD=YourStrongDatabasePassword123#
```

6. Run the database migrations & initial seeder:
   - **With SSH Terminal**:
     ```bash
     php artisan migrate --force --seed
     ```
   - **Without SSH (via cPanel phpMyAdmin)**:
     Export your local database `water_can_ordering` as `.sql` and import it directly into `cpaneluser_aquaflow` via phpMyAdmin.

---

## 4. File Permissions Configuration

Ensure the web server user (`nobody`, `apache`, or your cPanel user) has write permissions on the following folders:

```bash
chmod -R 775 storage bootstrap/cache
```

In cPanel File Manager:
- Right-click `storage` &rarr; **Change Permissions** &rarr; Set to `775` (or `755`).
- Right-click `bootstrap/cache` &rarr; **Change Permissions** &rarr; Set to `775` (or `755`).

---

## 5. Public Storage Symlink Creation

Public files (such as receipt attachments and product images) are stored in `storage/app/public` and exposed via `public/storage`.

Choose **one** of the following methods to establish the symlink:

### Method 1: Zero-SSH Admin Console (Recommended)
Log in to the Admin Dashboard as a Super Admin, go to your console, and send a POST request or click **Verify Storage Link** under System Utilities (or trigger `POST /admin/utilities/storage-link`).

### Method 2: cPanel Terminal (if enabled)
```bash
php artisan storage:link
```

### Method 3: One-Time PHP Script in cPanel File Manager
Create a temporary file `make_link.php` inside `public_html/`:
```php
<?php
symlink(__DIR__ . '/../storage/app/public', __DIR__ . '/storage');
echo "Link created!";
```
Visit `https://yourdomain.com/make_link.php` once in your browser, then delete the file.

---

## 6. cPanel Cron Job Configuration (Automated Schedules)

AquaFlow automatically schedules recurring subscription orders, maintains delivery slot capacities, and checks reconciliations daily.

1. Open cPanel &rarr; **Cron Jobs**.
2. Select **Once Per Minute** (`* * * * *`).
3. Enter the standard Laravel schedule runner command:

```bash
* * * * * cd /home/cpaneluser/aquaflow_core && php artisan schedule:run >> /dev/null 2>&1
```

*(Replace `/home/cpaneluser/aquaflow_core` with your actual absolute server directory path shown in cPanel).*

---

## 7. SSL Certificate & Razorpay Payment Webhook

1. Enable **Free AutoSSL / Let's Encrypt** in cPanel &rarr; **SSL/TLS Status** &rarr; Run AutoSSL.
2. In Razorpay Dashboard &rarr; **Settings** &rarr; **Webhooks**:
   - Webhook URL: `https://yourdomain.com/webhooks/razorpay`
   - Active Events: `payment.captured`, `payment.failed`, `order.paid`
   - Secret: Match the value configured in `RAZORPAY_WEBHOOK_SECRET` in `.env`.

---

## 8. Mobile PWA Installation Guide

- **Android (Chrome)**:
  1. Open `https://yourdomain.com`.
  2. Tap the browser menu (&vellip;) &rarr; **Install App** or tap the install banner.
  3. AquaFlow is installed as a standalone Android app with the splash icon and shortcuts.
- **iOS (Safari)**:
  1. Open `https://yourdomain.com`.
  2. Tap the Share button &rarr; **Add to Home Screen**.
- **Desktop (Chrome/Edge)**:
  1. Click the install icon in the URL bar &rarr; **Install AquaFlow**.

---

## 9. Maintenance & Cache Utilities

Whenever updating configurations, routes, or Blade templates:

```bash
php artisan optimize:clear
```
*(Or invoke via the Admin Console: `POST /admin/utilities/optimize-clear`).*
