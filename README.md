<div align="center">

# 💠 RekGo V4
### Sistem Rekber • Dashboard Laravel • Bot WhatsApp

**Dokumentasi developer berbahasa Indonesia**

[📖 Panduan Instalasi](#-instalasi-dari-nol) · [🧭 Alur Order](#-alur-order-rekber) · [🤖 Perintah Bot](#-daftar-perintah-bot) · [🛠️ Troubleshooting](#-troubleshooting)

</div>

> ⚠️ **Status: pengembangan / staging.** Sistem belum dinyatakan siap menerima dana rekber sungguhan. Pembayaran otomatis, pencairan, integrasi merchant, dan pairing WhatsApp belum lulus uji end-to-end. Jangan menganggap tombol atau tampilan sebagai bukti uang telah diterima.

## ✨ Apa itu RekGo?

**RekGo by CeirGo** adalah aplikasi untuk mengelola transaksi perantara (rekber). Pembeli dan penjual bertransaksi dalam grup WhatsApp, sementara identitas akun, pencatatan transaksi, verifikasi dan audit dikelola website Laravel. **Database bisnis hanya satu**, yakni MySQL/MariaDB milik website. Bot WhatsApp adalah proses Node.js terpisah yang berkomunikasi dengan API Laravel berautentikasi, bukan mengubah saldo langsung lewat SQL.

**Dokumentasi visual:** buka [docs/index.html](docs/index.html) di browser untuk panduan dengan CSS responsif. GitHub README tidak mendukung CSS kustom.

## 🧱 Teknologi yang digunakan

| Komponen | Bahasa / Teknologi | Tugas |
|---|---|---|
| Website & API | PHP 8.2+, Laravel 12, Blade | Login, akun, order, admin, audit, saldo |
| Database | MySQL / MariaDB kompatibel | Sumber data utama |
| Antarmuka | HTML, CSS, JavaScript | Dashboard responsif |
| Bot WhatsApp | Node.js 20+, JavaScript ES Modules, Baileys | Pesan grup, pairing, command |
| Pengelola bot | PM2 | Menjalankan dan mencatat log |
| Web server | Nginx, PHP-FPM, HTTPS | Melayani website |
| Payment bridge (opsional) | Node.js | Adapter merchant; **belum tervalidasi live** |

## 🏛️ Arsitektur sistem

```text
Browser Pengguna / Admin ── HTTPS ── Nginx ── Laravel ── MySQL
                                                ▲
                                                │ API Bearer Token
WhatsApp Grup ── Baileys ── Bot RekGo (PM2) ────┘
```

**Prinsip keamanan:** Laravel memegang validasi bisnis, saldo, status transaksi dan audit. Bot tidak menyimpan password MySQL. Port kontrol bot `127.0.0.1:4320` hanya boleh diakses lokal.

## 👥 Daftar peran pengguna

| Peran | Tanggung jawab | Kondisi saat ini |
|---|---|---|
| **Owner / Super Admin** | Pengaturan utama, audit, pengelolaan admin | Peran Laravel ada; **otorisasi Owner khusus bot belum ada** |
| **Admin / Finance** | Memeriksa order, pembayaran, WD, sengketa | Sebagian dashboard tersedia; workflow lengkap belum diuji |
| **Seller / Penjual** | Daftar akun, membuat grup, mengirim `/rekgo nominal` | Draft order via bot tersedia |
| **Buyer / Pembeli** | Daftar akun, membayar setelah instruksi resmi, `rekber done` | Permintaan selesai hanya diterima setelah status `paid` |
| **Bot RekGo** | Menerima command, memanggil API, mengirim notifikasi | Plugin dasar tersedia; integrasi WA belum terbukti stabil |

Nomor Owner dapat ditulis sebagai `OWNER_NUMBER=62...` pada `bot-rekgo/.env`, **tetapi belum menjadi mekanisme otorisasi aktif**. Jangan mengandalkan variabel ini untuk melindungi perintah finansial.

## 🧭 Alur order rekber

| Tahap | Aksi | Sistem | Status |
|---|---|---|---|
| 1. Registrasi | Seller dan Buyer membuat akun RekGo | Laravel mencocokkan nomor WhatsApp terdaftar | Fondasi tersedia |
| 2. Grup | Seller membuat grup WA berisi Buyer + Bot | Bot membaca command di grup | Tersedia dalam kode |
| 3. Buat order | Seller mengetik `/rekgo 10000` | Bot kirim API dengan ID pesan; Laravel membuat escrow `pending` dan menghitung fee | **Draft tersedia** |
| 4. Instruksi bayar | Buyer menunggu nominal dan tujuan pembayaran sah | Harus diterbitkan oleh adapter pembayaran terverifikasi | **Belum lengkap** |
| 5. Verifikasi dana | Provider mengonfirmasi referensi + nominal | Rekonsiliasi dan audit wajib sebelum status `paid` | **Belum teruji live** |
| 6. Barang/jasa | Seller memenuhi pesanan | Konfirmasi oleh pihak terkait | Prosedur operasional |
| 7. Konfirmasi | Buyer mengetik `rekber done` | API memeriksa bahwa escrow sudah `paid` dan buyer bukan seller | Sebagian tersedia |
| 8. Review | Admin memeriksa transaksi | Audit permintaan dibuat; **tidak otomatis mencairkan dana** | Sebagian tersedia |
| 9. Pencairan | Admin/penyedia pembayaran memproses settlement | Butuh konfirmasi transfer dan ledger valid | **Belum lengkap** |
| 10. Sengketa | Buyer/Seller melapor jika ada masalah | Perlu SOP bukti, mediasi, refund | **Belum lengkap** |

**Jam layanan admin yang direncanakan:** 09.00–20.00 WIB. Permintaan di luar jam tersebut diperiksa pada periode layanan berikutnya. Belum ada jaminan scheduler otomatis untuk kebijakan ini.

### Contoh percakapan grup

```text
Seller : /rekgo 10000
Bot    : 💠 RekGo • Transaksi #123
         Nominal: Rp10.000
         Biaya layanan: sesuai konfigurasi
         Status: Menunggu pembayaran
         ⚠️ Jangan transfer sebelum instruksi resmi terverifikasi.

Buyer  : rekber done
Bot    : Permintaan selesai diterima untuk pemeriksaan admin
         (hanya bila status pembayaran sudah paid).
```

## 🤖 Daftar perintah bot

| Perintah | Dikirim di | Hasil | Implementasi |
|---|---|---|---|
| `/menu`, `/start` | Grup | Menu dikirim via pesan pribadi | Ada |
| `/help`, `bantuan` | Grup | Panduan penggunaan via pesan pribadi | Ada |
| `akun`, `/akun` | Grup | Profil, saldo, bank tersamarkan, statistik via pesan pribadi | Ada |
| `/rekgo 10000` | Grup | Membuat draft order; nominal minimum API Rp1.000 | Ada |
| `rekber done` | Grup | Meminta pemeriksaan setelah pembayaran terverifikasi | Ada |
| `/status` | — | Rencana cek status transaksi | **Belum ada** |
| `/riwayat` | — | Rencana riwayat transaksi | **Belum ada** |
| `/admin` | — | Rencana fitur khusus admin/owner | **Belum ada** |

**Catatan:** Listener saat ini hanya menerima pesan **grup**; pesan pribadi tidak diproses sebagai command. Untuk `akun`, balasan dikirim ke chat pribadi pengirim. Akun tidak terdaftar ditolak oleh API Laravel. Nomor dengan JID `@lid` mungkin memerlukan resolusi nomor yang belum ditangani.

## 🗂️ Struktur folder

```text
/rekgo
├── app/
│   ├── Http/Controllers/      # Controller web, admin, bot API
│   ├── Models/                # Model database
│   └── Services/              # Wallet dan aturan bisnis
├── bootstrap/                 # Bootstrap Laravel
├── config/                    # Konfigurasi aplikasi
├── database/migrations/       # Perubahan skema
├── resources/                 # Blade dan CSS
├── routes/web.php             # Web dan API bot
├── public/                    # Document root Nginx
├── storage/                   # Log dan file privat
├── services/gopay-bridge/     # Bridge opsional, tidak siap live
├── bot-rekgo/
│   ├── index.js               # Socket WA + dispatcher
│   ├── plugins/commands.js    # Parser command + menu
│   ├── tests/commands.test.js # Unit test
│   ├── ecosystem.config.js    # PM2
│   ├── package.json
│   ├── .env                   # RAHASIA, jangan commit
│   └── auth/                  # Sesi WA, jangan commit
└── docs/index.html            # Dokumentasi visual
```

## 🚀 Instalasi dari nol

### 1. Siapkan VPS Debian 12

Butuh domain yang mengarah ke VPS, SSH, Nginx, MariaDB, PHP 8.2+, Composer 2, Node.js 20+ dan PM2.

```bash
sudo apt update
sudo apt install -y git curl unzip nginx mariadb-server composer \
 php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath
php -v
node -v
```

Jika Node belum terpasang atau versinya di bawah 20, instal versi LTS yang didukung dari sumber tepercaya sebelum melanjutkan.

### 2. Clone dan instal Laravel

```bash
git clone https://github.com/scardbypass/Rekgo.id.git /rekgo
cd /rekgo
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

**Buat database kosong dan akun SQL khusus:**

```sql
CREATE DATABASE rekgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rekgo_app'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON rekgo.* TO 'rekgo_app'@'localhost';
FLUSH PRIVILEGES;
```

Isi `/rekgo/.env`:

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
DB_USERNAME=rekgo_app
DB_PASSWORD=GANTI_PASSWORD_KUAT
REKGO_BOT_API_KEY=BUAT_SECRET_ACAK_MINIMAL_32_KARAKTER
GOPAY_LIVE_VERIFIED=false
```

**Hanya untuk instalasi BARU:**

```bash
cd /rekgo
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan route:list
```

**Upgrade VPS lama:** jangan menimpa `.env`, jangan menjalankan `key:generate` ulang, dan backup database sebelum migrasi. Jangan menjalankan seeder demo pada data finansial asli.

### 3. Nginx, PHP-FPM, dan HTTPS

Konfigurasikan server name `rekgo.my.id`, `root /rekgo/public;`, `index index.php;`, aturan Laravel `try_files $uri $uri/ /index.php?$query_string;`, dan `fastcgi_pass` ke socket PHP-FPM yang sesuai. Aktifkan HTTPS dan pastikan Nginx **tidak** mengakses file `.env`. Folder `storage` dan `bootstrap/cache` harus writable oleh PHP-FPM.

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Tambahkan cron Laravel:

```cron
* * * * * cd /rekgo && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### 4. Instal Bot WhatsApp terpisah

```bash
cd /rekgo/bot-rekgo
npm install
nano .env
```

Isi `/rekgo/bot-rekgo/.env`:

```dotenv
REKGO_API_URL=https://rekgo.my.id
REKGO_BOT_API_KEY=SECRET_YANG_SAMA_PERSIS_DENGAN_LARAVEL
REKGO_AUTH_DIR=./auth
REKGO_BOT_CONTROL_PORT=4320
REKGO_PAIR_PHONE=
OWNER_NUMBER=62XXXXXXXXXXX
```

`REKGO_PAIR_PHONE` dapat dikosongkan untuk menghindari percobaan pairing otomatis. `OWNER_NUMBER` belum diimplementasikan sebagai hak akses. **Jangan menambahkan password database ke file bot.**

```bash
cd /rekgo/bot-rekgo
npm install -g pm2
node --check index.js
node --check plugins/commands.js
npm test
pm2 start ecosystem.config.js
pm2 save
pm2 startup
pm2 logs rekgo-bot --lines 50
```

Ikuti perintah tambahan yang dicetak `pm2 startup`. PM2 tidak membutuhkan proses build untuk JavaScript ini. Pastikan **hanya satu proses** menggunakan folder `auth/` yang sama.

### 5. Uji sistem

```bash
cd /rekgo
php artisan migrate:status
php artisan route:list
php artisan test

cd /rekgo/bot-rekgo
node --check index.js
node --check plugins/commands.js
npm test
pm2 list
pm2 logs rekgo-bot --lines 50
```

Lakukan uji akun dan transaksi di database staging, bukan uang sungguhan. Pairing WhatsApp dengan Baileys belum diverifikasi berhasil; error `405` merupakan masalah koneksi/registrasi WA yang **tidak diselesaikan hanya dengan PM2**.

## 🔐 Keamanan wajib

- Jangan commit `.env`, sesi WA `auth/`, password, OTP, atau token.
- Jika rahasia muncul di screenshot, rotasi API key dan password DB. **Jangan asal mengganti APP_KEY Laravel** karena data terenkripsi dapat rusak.
- Gunakan HTTPS; batasi MySQL dan port kontrol bot ke localhost.
- Semua aksi keuangan melalui API Laravel, validasi server-side, audit dan idempotensi.
- Jangan menganggap draft escrow atau QR yang muncul sebagai bukti pembayaran.
- Butuh verifikasi penyedia pembayaran, rekonsiliasi, refund, penyelesaian sengketa, dan pemeriksaan izin sebelum menerima dana pelanggan.

## 🛠️ Troubleshooting

| Masalah | Pemeriksaan |
|---|---|
| Website HTTP 500 | `tail -n 100 /rekgo/storage/logs/laravel.log`, PHP-FPM, `php artisan migrate:status` |
| Bot offline | `pm2 list`, `pm2 logs rekgo-bot --lines 100` |
| API bot 401 | Samakan `REKGO_BOT_API_KEY` Laravel dan bot; `php artisan optimize:clear` |
| API bot 503 | Periksa socket WA, proses PM2, dan status koneksi |
| WhatsApp 405 | Periksa kompatibilitas Baileys/WA dan sesi; backup `auth/` sebelum reset |
| `akun` diam | Command hanya di grup; cek nomor terdaftar, JID, koneksi API, privasi DM |
| Tidak ada instruksi pembayaran | Memang belum tersedia adapter pembayaran live yang terverifikasi |
| Perubahan tidak muncul | `git pull --ff-only`, `php artisan optimize:clear`, `pm2 restart rekgo-bot --update-env` |

## ♻️ Update dan pemulihan

```bash
cd /rekgo
git status --short
# Backup DB + .env + sesi WA terlebih dahulu
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
cd /rekgo/bot-rekgo
npm install
npm test
pm2 restart rekgo-bot --update-env
```

Rollback migrasi finansial bisa merusak data; gunakan prosedur backup dan restore yang telah diuji, bukan `migrate:rollback` sembarangan.

## 🧪 Checklist developer berikutnya

- [ ] Uji semua route, controller, model, migrasi dan otorisasi pada database terisolasi.
- [ ] Uji pairing QR/kode dan reconnect WA dengan nomor uji.
- [ ] Implementasikan permission Owner/Admin pada bot, bukan hanya `.env`.
- [ ] Pecah handler bot ke plugin terpisah dan tambahkan test integrasi.
- [ ] Buat API status/riwayat, notifikasi admin, sengketa dan refund.
- [ ] Pastikan pembayaran dan pencairan hanya setelah bukti settlement yang sah.
- [ ] Uji duplikasi pesan, balapan transaksi, keamanan sesi, backup dan restore.
- [ ] Tinjau izin dan kepatuhan operasional jasa rekber.

**Catatan verifikasi:** Dokumentasi ini didasarkan pada kode yang ditinjau di GitHub. Tidak berarti instalasi VPS atau pembayaran live telah diuji dan berhasil.
