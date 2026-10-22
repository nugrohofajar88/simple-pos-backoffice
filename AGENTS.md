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
- **HPP (harga pokok): snapshot, bukan live-lookup.** `products.cost_price` = HPP terkini (bisa berubah kapan saja, mis. harga bahan naik). `order_items.cost_price` = snapshot HPP saat item itu dijual (di-copy dari `products.cost_price` saat order dibuat, lihat `OrderCreationService`/`SyncService::pushOrders()`) - order lama TETAP pakai HPP lama meski `products.cost_price` sudah berubah. `orders.hpp` = SUM(`order_items.cost_price * qty`) per order, disimpan di header biar laporan gak perlu join+sum tiap butuh angka profit order.
- **HPP dari resep (opsional, `ingredients` + `product_ingredients`).** `ingredients` = master bahan pokok (harga beli + jumlah + satuan -> `price_per_unit` accessor). `product_ingredients` = resep per produk (bahan + gramasi dipakai). `MenuService::recalculateProductCost()` nulis ulang `products.cost_price` = SUM(harga per satuan bahan x gramasi) - dipanggil otomatis tiap resep diedit (`addRecipeItem`/`updateRecipeItem`/`deleteRecipeItem`) ATAU harga beli bahan diubah (`updateIngredient`, nge-loop semua produk yg makai bahan itu). **`cost_price` TETAP field manual-editable biasa di form produk** (sengaja gak dibikin readonly/dikunci) - resep cuma nulis ke kolom yg sama, bukan sumber-kebenaran eksklusif. Produk tanpa baris `product_ingredients` ya tetap full manual kayak sebelumnya, gak ada migrasi paksa.
- SoftDeletes tetap ada di 4 model menu (buat kebutuhan web sendiri, mis. audit), TAPI query API/pullMenu selalu exclude yg trashed — mobile gak pernah lihat soft-deleted rows.

## Hosting — shared cPanel, proc_open & exec() DIMATIKAN
Host produksi mematikan **proc_open() dan exec()** (bukan cuma shell_exec) → dampak:
- `composer install/update` **selalu gagal** di step `post-autoload-dump` (`@php artisan package:discover` internal butuh proc_open). Workaround: biarkan gagal (paket sudah ke-install duluan), lalu manual: `composer dump-autoload --no-scripts --optimize` lalu `php artisan package:discover --ansi` langsung.
- `artisan storage:link` gagal (`Call to undefined function exec()`). Workaround: symlink manual `ln -sfn ../storage/app/public public/storage`.
- Scheduler: **JANGAN** `Schedule::command(...)` (butuh Symfony Process/proc_open) — pakai `Schedule::call(fn () => Artisan::call('db:backup'))` yang jalan in-process, lihat `routes/console.php`.
- Subdomain baru default PHP 8.3, project ini butuh **PHP 8.4** (composer.lock terkunci ke paket yg minta ≥8.4.1). Kalau tiba-tiba muncul error "Composer detected issues in your platform... require PHP >= 8.4.1" di browser, versi PHP-nya kebalik ke 8.3 lagi - **ada DUA sistem versi PHP terpisah di server ini, dua-duanya harus 8.4**:
  1. **cPanel MultiPHP (EasyApache, ea-phpXX)** - cek/ganti via `uapi LangPHP php_set_vhost_versions version=ea-php84 vhost=<subdomain>` (jalan dari SSH user biasa, gak perlu WHM/root). Pakai binary CLI eksplisit `/usr/local/bin/ea-php84` di server (PATH default `php` masih nunjuk ke versi lama).
  2. **CloudLinux PHP Selector (CageFS, alt-phpXX)** - ini yang BENERAN dipakai LiteSpeed buat nge-serve request, independen dari setting #1 di atas. Cek status: `selectorctl --interpreter=php --user-summary --user=<cpanel-user>`. Kalau baris `8.4` nunjukin flag `d` (disabled) padahal `e` (enabled) global, SET dulu: `selectorctl --interpreter=php --set-user-current -v 8.4 --user=<cpanel-user> --domain=<subdomain>`. **Kalau 8.4 disabled utk akun ini** (`enable-alternative` dicoba → error `"This command is not supported in CageFS"`), itu BUTUH root/WHM - gak bisa di-fix dari SSH user biasa, harus minta hosting provider aktifin `alt-php84` buat akun cPanel ini.
  - Cek efektif: `curl -I https://<subdomain>` → header `X-Powered-By` harus `PHP/8.4.x`. Jangan cuma percaya hasil `uapi`/`selectorctl` bilang sukses - selalu verifikasi header beneran berubah (dua sistem di atas bisa out-of-sync, salah satu "sukses" doang gak cukup).

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

## Pindah hosting/domain

Kodenya di GitHub, jadi bagian tersulit pindah host cuma **data** (database + file upload) dan **konfigurasi ulang**. Urutan:

1. **Clone kode** ke host baru: `git clone <repo>`, lalu `composer install --no-dev --optimize-autoloader` (lihat workaround proc_open di atas kalau host baru juga mematikannya).
2. **Restore database.** Backup harian sudah otomatis ke Google Drive lewat `db:backup` (lihat bagian Backup di atas) — unduh file `.sql.gz` terbaru dari folder `GOOGLE_DRIVE_FOLDER`, lalu:
   ```bash
   gunzip -c backup-file.sql.gz | mysql -u <user> -p <database_baru>
   ```
   **Catatan penting**: `db:backup` cuma dump database, **TIDAK termasuk file upload** (lihat poin 3) — jangan asumsikan file foto produk ikut ter-backup lewat jalur ini.
3. **Salin file upload** (`storage/app/public/products` — foto produk) manual dari host lama, mis. `rsync`/`scp`/download-upload via cPanel File Manager. Ini satu-satunya data yang TIDAK ter-backup otomatis di mana pun — jangan lupa sebelum host lama non-aktif.
4. **Buat `.env` baru**: salin dari host lama (kredensial DB baru, `APP_URL` baru, `GOOGLE_DRIVE_*`, `FONNTE_TOKEN`/`WABLAS_*` kalau dipakai) → `php artisan key:generate` HANYA kalau `.env` benar-benar baru dari nol (jangan generate ulang kalau APP_KEY lama masih dipakai, nanti data yang di-encrypt jadi tidak bisa dibuka).
5. **Storage & PHP version**: ikuti langkah-langkah di bagian "Hosting" di atas (symlink manual `storage:link`, pastikan PHP 8.4 aktif buat vhost baru).
6. **Cron**: pasang ulang `* * * * * php artisan schedule:run` di host baru (lihat bagian Scheduler di atas untuk contoh path PHP 8.4 eksplisit).
7. **DNS**: arahkan domain baru (atau domain lama kalau cuma pindah host) ke IP host baru, aktifkan SSL (AutoSSL via cPanel kalau hostingnya cPanel).
8. **Arahkan ulang client yang menunjuk ke backend ini** — ini sering kelupaan:
   - **App mobile** (`simple-pos`): Pengaturan → Sinkronisasi, ganti URL API & token ke yang baru (token API tetap sama kalau DB-nya hasil restore, tapi cek ulang).
   - **`pesenkopi`** (situs self-order): ganti `POS_API_BASE_URL` di `.env`-nya ke domain backend yang baru, lalu `php artisan config:clear` di sana.
9. **Uji**: login admin, cek menu ke-load, buat 1 order tes dari mobile/pesenkopi, cek WA (kalau dipakai), lalu jalankan `php artisan db:backup` manual sekali buat pastikan jalur backup ke Google Drive juga sudah nyambung dari host baru.
