# RekGo V4 — Laravel + MySQL

**Status: V4 development, bukan aplikasi escrow siap produksi.** Source ini adalah fondasi V3 yang diperluas dengan fitur V4. Versi README sebelumnya masih berjudul V3; identitas proyek kini diperbaiki. Penamaan V4 **bukan** klaim bahwa pembayaran live atau escrow end-to-end sudah siap.

## Fitur dan fungsi

| Modul | Fungsi | Status |
|---|---|---|
| Akun | Login, daftar, foto profil, rekening, pilihan pembayar fee | Fondasi tersedia |
| Dashboard User | Saldo tersedia, tertunda, terkunci WD; riwayat dan transaksi | Fondasi tersedia |
| Dashboard Admin | Pengguna, saldo, ACC WD, biaya, admin | Fondasi tersedia |
| Withdraw | Pending → Proses → Success; Rejected mengembalikan saldo locked sesuai ledger | **Transfer bank otomatis belum ada** |
| Escrow | Pembuatan draft transaksi dan aturan fee | **Penahanan/pelepasan dana nyata belum ada** |
| PWA | Manifest, ikon, offline page, cache aset statis | Kode tersedia |
| Maintenance | Admin ON/OFF, pesan; respons 503 untuk user biasa | Kode tersedia |
| Pengumuman | Popup, poster, jadwal, aktif/nonaktif | Kode tersedia |
| Integrasi WhatsApp | Daftar instance dan mode QR/pairing di Admin | **Neoxr/Baileys belum terhubung** |
| GoPay Bridge | Adapter Laravel → Node.js untuk SDK GoPay Merchant | SDK perlu dipasang secara sah; **belum diuji live** |
| QRIS User | Nominal, referensi provider, gambar QR dari payload, halaman privat | Kode tersedia; **belum diverifikasi merchant** |
| OTP Admin | Request dan verifikasi OTP via bridge | Kode tersedia; **belum diuji merchant** |
| Rekonsiliasi QRIS | Pemeriksaan pending, expiry/cancel, kredit ledger dengan referensi dan nominal cocok | Kode tersedia; perlu uji end-to-end dan pembayaran terlambat |
| Audit | Pencatatan perubahan finansial/admin | Fondasi tersedia |

## Teknologi

- PHP 8.2+, Laravel 12, MySQL 8+ atau MariaDB kompatibel, Composer 2.
- Node.js 20+ **diperlukan untuk GoPay Bridge**, bukan untuk CSS Laravel.
- CSS responsive glossy/light-dark; `resources/css/` dan Blade.
- Nginx, PHP-FPM, HTTPS, cron untuk Laravel Scheduler.
- SDK GoPay pihak ketiga **tidak didistribusikan** karena ketentuan lisensinya harus dihormati.

## Instalasi Debian 12 / VPS (development)

```bash
sudo apt update
sudo apt install -y git unzip nginx mariadb-server composer php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath
git clone https://github.com/scardbypass/Rekgo.id.git
cd Rekgo.id
composer install
cp .env.example .env
php artisan key:generate
```

Buat database dan user MySQL dengan hak minimum. Edit `.env` untuk `APP_NAME=RekGo`, `APP_URL`, `APP_ENV`, `APP_DEBUG=false`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. **Jangan commit `.env`.**

```bash
php artisan migrate --seed
php artisan storage:link
php artisan route:list
php artisan serve --host=127.0.0.1 --port=8000
```

Untuk production, Nginx `root` harus menunjuk ke `/path/to/Rekgo.id/public`; jangan mengekspos folder proyek, `.env`, token, sesi WhatsApp, atau GoPay Bridge. Pastikan izin tulis hanya pada `storage` dan `bootstrap/cache`, gunakan TLS dan PHP-FPM.

Scheduler:

```cron
* * * * * cd /path/to/Rekgo.id && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

`rekgo:poll-qris` hanya mencari pembayaran pending yang jatuh tempo dicek. Penonaktifan gateway dan ketidakpastian respons provider harus selalu ditangani secara aman. `rekgo:cleanup-proofs` dinonaktifkan secara efektif lewat `REKGO_PROOF_CLEANUP_ENABLED=false` hingga legal hold dan retensi diuji.

### Membuat Super Admin dari server

```bash
php artisan tinker
```

```php
$u = App\Models\User::create([
 'name' => 'Owner RekGo',
 'username' => 'owner',
 'email' => 'owner@example.com',
 'password' => Illuminate\Support\Facades\Hash::make('GANTI_PASSWORD_KUAT_UNIK'),
 'role' => 'super_admin',
]);
```

Jangan gunakan contoh password di atas. Jangan menjalankan seed demo pada database uang nyata.

## Pengaturan aplikasi

- `/admin`: dashboard keuangan, WD dan fee.
- `/admin/v4`: maintenance dan pengumuman.
- `/admin/integrations`: instance WA, pengaturan PG, request/verify OTP merchant.
- `/dashboard`: saldo dan transaksi user.
- `/qris`: membuat QRIS bila integrasi provider dinyalakan secara sah dan tervalidasi.
- `/qris/{payment}`: detail QR privat; `/qris/{payment}/image.svg`: gambar QR dari payload yang diterbitkan provider.

**Pengaturan GoPay:** jalankan `services/gopay-bridge/` sesuai README di folder tersebut; SDK terlisensi dipasang terpisah. Bridge hanya boleh listen di localhost dan harus memakai autentikasi internal. Rahasiakan OTP, token dan sesi; verifikasi outlet, format respons, transaksi duplikat, late settlement, cancel, refund dan rekonsiliasi sebelum live. `GOPAY_LIVE_VERIFIED=false` harus tetap menjadi default sampai semua pengujian selesai.

**Withdraw:** bank inquiry asli belum tersedia. Tombol Success tidak menggantikan konfirmasi transfer nyata. Nomor rekening penuh hanya boleh diakses Finance/Super Admin. Fee platform tidak boleh dihitung sebagai saldo user; MDR QRIS tidak boleh dialihkan kepada konsumen.

## Struktur folder

```text
app/              Laravel controller, model, service dan command
bootstrap/        Laravel bootstrap
config/           Konfigurasi
database/         Migration dan seeder
public/           Aset publik, manifest/PWA
resources/        Blade, CSS, aset UI
routes/           Web route dan scheduler
services/         Node.js GoPay Bridge
storage/          File runtime privat
tests/            Pengujian
```

## Pemeriksaan

```bash
php artisan route:list
php artisan migrate:status
php artisan test
```

Belum ada bukti tes end-to-end dengan MySQL, akun merchant nyata, transfer bank, atau WhatsApp. Jangan gunakan untuk menerima atau meneruskan dana pelanggan sebelum izin/kerja sama penyedia layanan, audit keamanan, idempotensi ledger, rekonsiliasi, dispute/refund, 2FA admin, backup/restore, dan tes produksi dipenuhi.

## Riwayat

- V3: fondasi user, saldo, WD, escrow draft.
- V4: PWA, maintenance, pengumuman, pengaturan integrasi, adapter GoPay, OTP, QRIS grafik, expiry dan rekonsiliasi dasar.
- 14 Oktober 2026: dokumentasi diperbaiki agar tidak lagi mengklaim V3 sebagai versi utama dan membedakan kode tersedia dari fitur yang benar-benar teruji.
