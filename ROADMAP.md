# ROADMAP.md — simple-pos-backoffice

Catatan rencana ke depan. Saat ini project **single-tenant** (1 bisnis, 1 admin, 1 token API) — dan itu sudah cukup untuk kebutuhan sekarang. Bagian di bawah ini cuma catatan kalau suatu saat mau dikembangkan jadi **SaaS** (banyak bisnis/kafe pakai instance yang sama). Belum dikerjakan, belum jadi keputusan final — sekadar arah biar gak lupa titik-titik yang perlu diubah.

## Kenapa perlu berubah

Sekarang semua tabel (`categories`, `products`, `orders`, `expenses`, dst) itu global — cuma ada 1 "toko" di seluruh database. Kalau mau banyak bisnis pakai 1 instance yang sama, setiap baris data harus tahu "punya siapa".

## Area yang perlu berubah

1. **Multi-tenancy (paling besar)**
   - Tambah `tenant_id` (atau `store_id`) di semua tabel bisnis: categories, products, modifier_groups, modifier_options, orders, order_items, order_item_modifiers, expenses, settings.
   - Semua query di `MenuService`/`SyncService`/`ReportService`/controller di-scope per tenant — bisa pakai [global scope Eloquent](https://laravel.com/docs/eloquent#global-scopes) biar gak lupa nambah `where tenant_id` di tiap query manual.
   - Pilihan model: **shared database + tenant_id column** (lebih murah/simpel, cocok skala kecil-menengah) vs **database per tenant** (lebih isolated tapi lebih ribet ops). Rekomendasi mulai dari yang pertama dulu.

2. **Auth & user management**
   - Dari 1 admin user jadi banyak akun, tiap akun terikat ke 1 (atau lebih) tenant.
   - Perlu flow **signup/onboarding** (bikin tenant baru + akun admin pertama) — sekarang gak ada sama sekali (admin di-seed manual).
   - Mungkin butuh role (owner/kasir/dst) kalau 1 tenant bisa punya lebih dari 1 user.

3. **Auth mobile ↔ API**
   - Sekarang: 1 Sanctum token statis, digenerate manual dari halaman Settings, ditempel manual ke app.
   - Nanti: token per-tenant (sudah otomatis ke-handle kalau token tetap terikat ke `User` yang sudah punya `tenant_id`), tapi perlu mikirin: gimana app mobile tahu ini token buat tenant yang mana kalau 1 HP cuma dipasangkan ke 1 tenant (biasanya gitu, jarang 1 device dipakai lintas tenant).

4. **Billing (kalau mau berbayar)**
   - Integrasi payment gateway (mis. Midtrans/Xendit, konsisten sama yang biasa dipakai project lain), langganan per tenant, batasi fitur/kuota per plan.
   - Di luar scope teknis murni — ini keputusan bisnis dulu (harga, model langganan) sebelum ada kerjaan teknis.

5. **Isolasi resource**
   - Backup (`db:backup`) perlu mikirin per-tenant atau tetap 1 backup gabungan (tergantung model DB di atas).
   - Rate limiting/kuota API per tenant biar 1 tenant gak bisa "menghabiskan" resource shared server.

## Yang TIDAK perlu berubah

- Arsitektur inti (REST API, `MenuService`/`SyncService`/`ReportService` sebagai service layer terpisah dari controller) sudah cukup rapi buat direfactor ke arah ini — gak perlu rombak total, tinggal nambah dimensi `tenant_id` di layer yang sudah ada.
- Model sync di mobile (outbox order/belanja, cache read-only menu) gak perlu berubah sama sekali — itu urusan per-device, gak terkait multi-tenancy.

## Kapan mulai

Belum sekarang — project ini masih dipakai pribadi (1 bisnis). Baru relevan kalau ada rencana konkret buat menjual/menyewakan ke bisnis lain.
