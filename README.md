# RekGo V4 — Development Repository

**Status: belum siap produksi.** Source lengkap RekGo V4 dari paket lokal belum seluruhnya terunggah ke repository ini. Jangan gunakan untuk menerima uang sungguhan.

## Stack
Laravel 12, PHP 8.2+, MySQL/MariaDB, CSS Liquid Glass, Node.js GoPay Bridge (SDK terpisah sesuai lisensi).

## Fitur yang dirancang
- Dashboard user: saldo tersedia, tertunda, terkunci WD; transaksi, profil, rekening.
- Super Admin: manajemen user, transaksi, fee, pengumuman, maintenance, role Finance/Support.
- Withdraw: Pending → Proses → Success atau Rejected, bukti dan audit.
- QRIS: nominal dinamis, QR grafis, expiry 10 menit, pembatalan dan rekonsiliasi.
- GoPay Merchant: OTP login, outlet, status QRIS melalui bridge privat.
- PWA: manifest, offline static assets, tema terang/gelap.
- WhatsApp Neoxr/Baileys: **belum terintegrasi**.

## Instalasi (setelah source lengkap)
```bash
sudo apt update
sudo apt install -y nginx mariadb-server git unzip php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath composer
git clone https://github.com/scardbypass/Rekgo.id.git
cd Rekgo.id
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Konfigurasi APP_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD secara privat
php artisan migrate --force
php artisan storage:link
php artisan optimize
```
Nginx harus mengarah ke folder `public/`, bukan root proyek. Jalankan scheduler tiap menit:
```cron
* * * * * cd /path/to/Rekgo.id && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```
**Catatan:** Perintah ini belum dapat dijalankan pada repository yang belum berisi seluruh aplikasi.

## Pengaturan keamanan
Rahasiakan `.env`, OTP, token GoPay, database credentials dan sesi WhatsApp. Gunakan HTTPS; bridge hanya localhost; otorisasi Finance/Super Admin untuk nomor rekening lengkap; jangan alihkan MDR QRIS kepada konsumen. Jangan aktifkan pembayaran live sebelum provider, legalitas, idempotensi ledger, bank inquiry, refund, dan rekonsiliasi diuji.

## Status upload
README dan beberapa file awal diunggah melalui konektor GitHub. Source aplikasi lengkap masih dalam paket lokal dan **belum berhasil dipindahkan seluruhnya ke GitHub**.
