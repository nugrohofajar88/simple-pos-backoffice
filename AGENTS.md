# AGENTS.md — simple-pos-backoffice

Backend **full REST API** buat app mobile POS kafe (`simple-pos`, Expo/React Native + SQLite). **Server ini satu-satunya sumber kebenaran** untuk menu & laporan — mobile TIDAK local-first lagi utk itu (baca live dari API tiap layar dibuka). SQLite lokal di mobile cuma dipakai sbg: (1) outbox order/belanja yg belum ke-sync (checkout tetap SELALU jalan offline), (2) cache read-only menu buat fallback pas gak ada internet. Edit menu dari mobile WAJIB online (langsung API call, gak ada edit lokal).

- Stack: **Laravel 12, PHP 8.4, MySQL, Sanctum** (API auth), **Blade + Alpine.js** (dashboard, CDN only, no build step).
- Produksi: `https://pos.fajarnugroho.info`.
- 1 admin user (login web via `auth` guard standar), 1 Sanctum personal access token statis (digenerate dari `/settings`) buat mobile↔API.

## Menjalankan
```bash
php artisan serve
php artisan migrate --seed
php artisan storage:link
```
Setelah ubah `.env`: **`php artisan config:clear`**.

## Arsitektur & konvensi
- `casts()` method (bukan `$casts` property), relasi Eloquent bertipe, validasi inline `$request->validate()` di controller (no Form Request), logika bisnis di `app/Support/*Service.php`.
- **`MenuService`** — create/update/delete kategori/produk/modifier, dipakai LANGSUNG oleh controller web (`app/Http/Controllers/*`, redirect) DAN controller API (`app/Http/Controllers/Api/*`, JSON) — satu source of truth logic, beda cuma response shape.
- **`SyncService`** — `pullMenu()` (snapshot penuh menu aktif, dipanggil `GET /api/menu`), `pushOrders()`/`pushExpenses()` (terima outbox dari mobile, idempotent — order dicek by `order_number` unique, product_id yg gak valid di-null-in bukan gagal total).
- **`ReportService`** — dipakai `DashboardController` (web) DAN `Api\ReportController@summary` (`GET /api/reports/summary`, dikonsumsi mobile langsung) — angka difilter dari `mobile_created_at` (waktu asli di HP), BUKAN `created_at` Laravel.

## Model data (PENTING)
- **Menu (categories/products/modifier_groups/modifier_options): server mutlak sumber kebenaran.** Edit dari web ATAU dari mobile (kalau online) sama-sama langsung ke `MenuService`, gak ada "sync 2 arah"/Last-Write-Wins lagi — mobile cuma nyimpen cache read-only hasil `GET /api/menu`, id di cache mobile = id server LANGSUNG (gak ada mapping `remoteId` lagi).
- **Orders/order_items/order_item_modifiers/expenses: push 1 arah** mobile→server (mobile bikin dulu offline-safe, kirim pas online — order immutable begitu dibuat, gak ada UI edit order di mobile atau di sini). Kolom `remote_id` di mobile cuma dipakai buat order/expense (flag "sudah/belum ke-sync"), BUKAN buat menu lagi.
- `settings` (store_name, initial_capital) di-push 1 arah dari mobile, ditampilkan read-only di web.
- SoftDeletes tetap ada di 4 model menu (buat kebutuhan web sendiri, mis. audit), TAPI query API/pullMenu selalu exclude yg trashed — mobile gak pernah lihat soft-deleted rows.

## Hosting — shared cPanel, proc_open & exec() DIMATIKAN
Host produksi mematikan **proc_open() dan exec()** (bukan cuma shell_exec) → dampak:
- `composer install/update` **selalu gagal** di step `post-autoload-dump` (`@php artisan package:discover` internal butuh proc_open). Workaround: biarkan gagal (paket sudah ke-install duluan), lalu manual: `composer dump-autoload --no-scripts --optimize` lalu `php artisan package:discover --ansi` langsung.
- `artisan storage:link` gagal (`Call to undefined function exec()`). Workaround: symlink manual `ln -sfn ../storage/app/public public/storage`.
- Scheduler: **JANGAN** `Schedule::command(...)` (butuh Symfony Process/proc_open) — pakai `Schedule::call(fn () => Artisan::call('db:backup'))` yang jalan in-process, lihat `routes/console.php`.
- Subdomain baru default PHP 8.3, project ini butuh **PHP 8.4** (composer.lock terkunci ke paket yg minta ≥8.4.1). Ganti versi per-vhost via `uapi LangPHP php_set_vhost_versions version=ea-php84 vhost=<subdomain>` (jalan dari SSH user, gak perlu WHM/root). Pakai binary CLI eksplisit `/usr/local/bin/ea-php84` di server (PATH default `php` masih nunjuk ke versi lama).

## Backup
`artisan db:backup` — dump database **pure-PHP** (`ifsnop/mysqldump-php`, gzip, TANPA shell `mysqldump`) lalu upload ke disk `gdrive` (`masbug/flysystem-google-drive-ext`). Opsi `--disk=gdrive --keep=14` (retensi hari). Butuh `.env`: `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_REFRESH_TOKEN`, `GOOGLE_DRIVE_FOLDER`. Dijadwalkan harian 02:00 (`routes/console.php`).

## Scheduler (butuh cron di hosting)
`routes/console.php`: `db:backup` (harian 02:00 → Google Drive).
Wajib cron OS: **`* * * * * php artisan schedule:run`** (tiap menit; jadwal detail diatur Laravel). Contoh baris cron yang dipakai di produksi (path PHP 8.4 eksplisit):
```
* * * * * /usr/local/bin/ea-php84 /home/<user>/public_html/pos.fajarnugroho.info/artisan schedule:run >> /dev/null 2>&1
```

## Deploy
`git pull` → `composer install --no-dev --optimize-autoloader` (lihat workaround proc_open di atas kalau hang di post-script) → `php artisan migrate --force` → `php artisan config:clear`.
Produksi: `APP_ENV=production`, `APP_DEBUG=false`.
`.env` gitignored — jangan commit secret; kalau perlu berbagi, sensor nilainya.
