# RekGo V3 — Laravel + MySQL

Source code untuk **fondasi aplikasi RekGo** dengan UI responsive glossy/light-dark, autentikasi, user dashboard, foto profil, preferensi fee, rekening pencairan, pencatatan pengajuan WD, ACC admin (Pending → Proses → Success / Rejected), audit, fee tiers, draft transaksi rekber dan draft QRIS.

> **PENTING: BUKAN sistem escrow siap produksi.** Tidak ada QRIS yang dapat dibayar, bank inquiry asli, auto transfer, WhatsApp bot, dispute engine, payout reconciliation, callback payment gateway, bank settlement, 2FA, reset/invitation email, atau pengelolaan keuangan menyeluruh. Modul transaksi rekber baru membuat draft, **belum menerima/menahan/melepas dana**. Jangan terima dana pelanggan nyata dengan paket ini sebelum integrasi provider berizin, audit keamanan, uji integrasi dan kepatuhan.

## Prasyarat

- PHP 8.2+ dengan ekstensi mbstring, openssl, PDO MySQL, tokenizer, xml, ctype, fileinfo
- Composer 2, MySQL 8+, Node **tidak diperlukan** (CSS inline)
- Laravel 12; folder `vendor/` sengaja tidak disertakan, diunduh oleh Composer.

## Instalasi

```bash
unzip rekgo-v3.zip
cd rekgo-v3
composer install --no-interaction
cp .env.example .env
php artisan key:generate
# Buat database dan user MySQL, kemudian isi DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

Jalankan scheduler setiap menit dari cron:

```cron
* * * * * cd /path/to/rekgo-v3 && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Buat Super Admin **hanya dari terminal server** (jangan memasukkan password dalam source/repo):

```bash
php artisan tinker
```

```php
$u = App\Models\User::create(['name'=>'Owner RekGo','username'=>'owner','email'=>'owner@example.com','password'=>Illuminate\Support\Str::random(48),'role'=>'super_admin']);
$u->password = Illuminate\Support\Facades\Hash::make('GANTI_DENGAN_PASSWORD_ACAK_PANJANG');
$u->save();
```

Jangan gunakan password contoh. Akses `/login`, `/dashboard`, `/admin`.

## Database

Migrasi `database/migrations/2026_01_01_000001_create_rekgo_tables.php` membuat `users`, `balances`, `withdrawals`, `escrows`, `payments`, `fee_tiers`, `ledgers`, `audits`, `sessions`, `password_reset_tokens`. Seed menyediakan fee demo. Semua nominal uang adalah integer rupiah. Ledger append-only pada alur normal, dan pemindahan saldo dilakukan dalam DB transaction dengan row lock.

## Perilaku WD

- User hanya boleh mengajukan WD jika rekening telah diverifikasi secara **nyata** (belum ada bank inquiry connector; maka WD user baru tetap terkunci).
- Pending: pindahkan saldo tersedia ke locked.
- Proses: admin Finance menyetujui; dana tetap locked.
- Success: hanya setelah transfer nyata dibuktikan/rekonsiliasi, keluarkan locked; bukti file opsional, referensi bank wajib.
- Rejected: hanya sebelum proses; kembalikan locked ke available. Tidak ada pembatalan buta setelah bank processing.
- Sistem **tidak mengirim transfer bank** dan tidak boleh menganggap tombol Success sebagai bukti transfer bank.

## Fee

- Seller memilih buyer/seller untuk fee rekber; pilihan tersimpan saat draft transaksi dibuat.
- QRIS Web punya pilihan payer sendiri; fee MDR QRIS tidak boleh dialihkan ke konsumen. Sebelum aktif, provider dan legal harus memvalidasi apakah biaya layanan terpisah boleh dibebankan.
- Tier persentase disimpan dalam basis poin: `100` = 1%; contoh 5000 minimum → 2000 flat, 100000 minimum → 5000 flat.
- Fee platform **tidak** masuk balance user. Revenue accounting, refund and settlement masih memerlukan integrasi lanjutan.

## Keamanan dan keterbatasan

- Autentikasi Laravel, session regeneration, CSRF, request validation, rate limiting dasar.
- Rekening penuh hanya ditampilkan ke Super Admin/Finance; Support tidak dapat melihatnya. File bukti disimpan di disk private dan hanya dapat diunduh oleh Finance/Super Admin.
- Akun admin baru dibuat dengan password acak yang tidak diberikan kepada siapa pun: **belum bisa login** sampai undangan aktivasi/reset password yang aman diimplementasikan.
- Foto profil disimpan di `public` disk; file divalidasi tipe dan ukuran. Untuk produksi, lakukan pemrosesan ulang gambar dan pemindaian file.
- Cleanup bukti setelah minimum 45 hari tersedia, namun **legal hold/sengketa belum diimplementasikan**; jangan aktifkan scheduler cleanup pada produksi dengan kewajiban retensi sampai sistem hold tersedia.
- Untuk produksi wajib tambahkan 2FA admin, granular permissions, bank inquiry, webhook signature verification, idempotency keys, reconciliation, dispute holds, balance invariants, refund engine, notification queues, backups encrypted, observability, full testing dan regulatory review.

## Struktur

`app/Http/Controllers` untuk HTTP, `app/Models` untuk data, `app/Services/Wallet.php` untuk ledger/fee, `database/migrations` untuk schema, `database/seeders` untuk fee demo, `resources/views` untuk UI, `routes` untuk route dan scheduler, `app/Console/Commands` untuk retensi file.

## Cek kode

```bash
find app database routes bootstrap public -name '*.php' -print0 | xargs -0 -n1 php -l
php artisan route:list
php artisan migrate:status
```

**Status:** source fondasi dapat dipasang setelah Composer dependencies diunduh; belum menjalani pengujian end-to-end dengan MySQL/payment gateway di lingkungan ini.


## RekGo V4 additions (implemented)
- PWA manifest, basic offline page, installable icons, static-only service worker.
- Super Admin `/admin/v4`: maintenance toggle and message, announcement poster upload (5 MB), scheduling, enable/disable, deletion.
- Authenticated users see one active unread popup; dismissals are stored per user.
- Migration `2026_10_14_000002_create_v4_content_tables.php`; run `php artisan migrate` and `php artisan storage:link`.
- Maintenance returns HTTP 503 to normal users. Verify payment callbacks and reconciliation separately before enabling in production.

## Not yet integrated / production limitations
- Neoxr/Baileys multi-session, QR/pairing admin panel, GoPay MARC QRIS creation and reconciliation are **not implemented**. Never process live funds with this starter.
- Existing V3 withdrawal requires actual bank beneficiary verification; no bank inquiry provider is included.
- Production requires provider integration, idempotent payment verification, risk/compliance review, comprehensive tests, and operational monitoring.

## Update integrasi V4 (14 Oktober 2026)

- Migration `2026_10_14_000003_create_integrations_tables.php` membuat tabel `wa_bot_instances` dan `payment_provider_settings`.
- Buka `/admin/integrations` sebagai `super_admin` untuk mendaftarkan beberapa nomor WhatsApp, memilih QR/pairing, mengaktifkan/menonaktifkan registrasi bot, dan mengatur draft GoPay MARC On-Demand.
- **PENTING:** nomor yang terdaftar **tidak** otomatis online. Neoxr/Baileys belum terpasang; QR dan pairing belum dapat dihasilkan.
- **PENTING:** pembayaran GoPay MARC **tidak** diaktifkan. Form sengaja menolak `enabled=1` sampai gateway resmi/berizin, validasi transaksi dan rekonsiliasi selesai.
- Tidak ada akses token, password merchant, atau sesi WhatsApp yang disimpan lewat form ini.
- Untuk live: audit lisensi/struktur Neoxr, jalankan Node.js service terpisah, session encryption, authenticated internal bridge, pairing challenge expiry, reconnect handling, signed callback, queue, idempotent journal, provider reconciliation dan pengujian end-to-end.
- Semua perubahan integrasi dicatat pada audit log. Pastikan `php artisan migrate` dijalankan setelah memperbarui kode.

### Checklist yang masih diperlukan sebelum produksi
1. Mengambil source Neoxr dari GitHub (git clone gagal di lingkungan pembuatan karena DNS), verifikasi lisensi, kompatibilitas Baileys, dan membuat bridge QR/pairing multi-sesi yang benar.
2. Memperoleh dan menguji akses GoPay Merchant/MARC yang diizinkan, membuat QRIS nyata dan memverifikasi pembayaran melalui metode provider yang sah.
3. Mengimplementasikan bank inquiry, konfirmasi transfer dan rekonsiliasi, notifikasi WA, penanganan sengketa/refund, pengamanan role granular, 2FA, serta pengujian transaksi finansial.
4. Memasang dan menguji seluruh aplikasi di VPS dengan database sungguhan; saat ini baru PHP syntax lint yang dijalankan, bukan test end-to-end.


## Safety update / completion blockers

- Transfer proof cleanup now **fails closed** (`REKGO_PROOF_CLEANUP_ENABLED=false` by default) because dispute/legal-hold protections are not yet implemented. Enabling it before adding and testing hold exclusions is unsafe.
- Real QRIS, WhatsApp pairing, and beneficiary bank inquiry require verified provider implementations and credentials. The current admin forms only save draft settings; they do not connect those services.
- No successful production end-to-end payment, payout, or WhatsApp tests have been performed. Do not deploy as a live escrow operator.

## GoPay Merchant SDK integration bridge (new)

`services/gopay-bridge/` contains a local-only Node.js adapter to the GMA SDK supplied by the user. The third-party SDK has restrictive license terms and is **not redistributed** in this ZIP. See `services/gopay-bridge/README.md` for setup and security requirements. `App\\Services\\GopayBridge` provides an HTTP client in Laravel. The PG remains **disabled** in Admin, and user QRIS remains **draft only**, pending authorized credentials, verified provider responses, transaction mapping, ledger-safe settlement, and end-to-end testing. This is an integration foundation, **not a working live payment gateway**.


## GoPay V4 payment workflow (experimental; live OFF)
- New migration `2026_10_14_000004_gopay_live_fields.php`: QR payload, status checks and timestamps.
- User `/qris` calls `GopayPayments::create` through localhost bridge only when provider is enabled. If SDK response keys do not match the strict parser, QR is not published.
- Admin `/admin/integrations` has OTP request and verification forms; OTP session token is encrypted, access/refresh tokens stay in the bridge service.
- `php artisan rekgo:poll-qris` is scheduled every minute, no provider request if no active pending QRIS. It does not mark invoices expired automatically because provider reconciliation/cancellation is still required.
- Credit is applied only when provider status, exact reference, and gross amount all match, under row lock and DB transaction. Any ambiguous provider response is quarantined for manual reconciliation, never auto-credited.
- QR payload is currently displayed as text in user history, **not yet rendered as a scannable QR graphic**. Bundle and test a QR renderer before launch.
- SDK response schemas, payment expiry/cancel and provider reconciliation still require end-to-end tests. `GOPAY_LIVE_VERIFIED=false` must remain the default; changing it does not constitute proof of compliance or production readiness.
- Do not pass QRIS MDR to buyers. Sender-paid QRIS fees are rejected until a legally compliant separate service fee is reviewed.
- Install licensed SDK separately, and never commit credentials or token files.


## QRIS graphics + expiry reconciliation update
- Composer dependency: `endroid/qr-code:^6.0`. Run `composer update endroid/qr-code` or install dependencies before deploying.
- `/qris/{payment}` displays a private, authenticated QRIS detail; `/qris/{payment}/image.svg` renders the *provider-issued* EMV QR payload locally as a scannable SVG (no third-party QR image API).
- QR images are never served after their local expiry or after payment stops being pending. Status remains pending until the provider confirms a terminal state.
- Scheduler: for expired pending payments, verify provider state, request cancellation, verify the terminal cancelled/expired state, then mark local expired. Ambiguous or failed responses stay pending for retry/manual review. Paid invoices are settled via the existing strict reference+amount verification.
- WARNING: provider status/cancel response schemas and actual QR scanning MUST be validated against licensed SDK and real test merchant. A late settlement after confirmed cancellation, webhook reconciliation, concurrency and provider reconciliation reports still require end-to-end testing. Keep real payments OFF until approved.
- Cron: `* * * * * cd /path/to/rekgo && php artisan schedule:run >> /dev/null 2>&1`. Never expose the localhost GoPay bridge publicly.
