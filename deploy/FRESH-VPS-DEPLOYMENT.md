# RekGo V4 — VPS fresh (Debian 12)

> Panduan, **bukan** bukti bahwa server sudah terpasang. Jangan mengaktifkan QRIS live. Domain: rekgo.my.id.

## 1. Persiapan DNS Cloudflare

Buat A record `@` ke IPv4 publik VPS. Jika memakai IPv6, buat AAAA hanya jika IPv6 VPS benar-benar berfungsi. Set SSL/TLS **Full (strict)** setelah memasang sertifikat origin yang valid. Untuk pengujian awal gunakan DNS-only sampai origin dapat diakses, kemudian aktifkan proxy jika diinginkan. Jangan gunakan Flexible.

## 2. Paket sistem

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mariadb-server git unzip curl ca-certificates software-properties-common certbot python3-certbot-nginx supervisor
# PHP 8.2 tersedia pada Debian 12 dan memenuhi syarat proyek Laravel.
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl
php -v
```

Install Composer dari situs resminya dengan **verifikasi checksum installer** sesuai dokumentasi https://getcomposer.org/download/; jangan menjalankan skrip unduhan tanpa verifikasi.

## 3. Aplikasi

```bash
sudo mkdir -p /var/www/rekgo
sudo chown "$USER":www-data /var/www/rekgo
git clone https://github.com/scardbypass/Rekgo.id.git /var/www/rekgo
cd /var/www/rekgo
cp .env.example .env
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan key:generate
```

Set di `.env` melalui editor lokal aman:

```dotenv
APP_NAME=RekGo
APP_ENV=production
APP_DEBUG=false
APP_URL=https://rekgo.my.id
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rekgo
DB_USERNAME=rekgo
DB_PASSWORD=REPLACE_WITH_STRONG_LOCAL_SECRET
GOPAY_LIVE_VERIFIED=false
REKGO_PROOF_CLEANUP_ENABLED=false
GOPAY_BRIDGE_URL=http://127.0.0.1:4319
```

Create a dedicated database/user in MariaDB with a **unique strong password** and only privileges for the RekGo database. Never commit secrets. Set ownership/permissions so PHP-FPM can write only to `storage` and `bootstrap/cache`; do not make the entire project world-writable.

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Nginx

Use `deploy/nginx/rekgo.my.id.conf.example` as a template. Debian 12 uses `/run/php/php8.2-fpm.sock` by default; **replace** the example's php8.3 socket. Install a valid certificate for the origin (Let's Encrypt with DNS-only validation, or Cloudflare Origin CA when proxied). Verify:

```bash
sudo nginx -t
sudo systemctl reload nginx
curl -I https://rekgo.my.id
```

## 5. Background jobs

Review `php artisan schedule:list` and the actual queue configuration before enabling Supervisor. Add a system cron for `php artisan schedule:run` once per minute using the deploy user. Do not run duplicate QRIS settlement workers. Configure backups for database, uploads and encryption keys with secure offsite retention.

## 6. Acceptance gate

- DNS points to correct VPS; TLS verified; no public DB port.
- `php artisan route:list`, migrations and tests pass against a **separate** test DB.
- Registration/login/roles verified; uploaded files not publicly accessible if private.
- QRIS disabled; all provider actions tested via local mocks before official merchant sandbox.
- Withdrawal, escrow, refunds and dispute flow must pass integration tests before customer funds are accepted.

**Production readiness is NOT claimed by this deployment guide.**
