# RekGo V4 — Developer Guide

> **Status: development / staging. NOT approved for real-money escrow operations.** Code availability is not proof of operational readiness, legal compliance, or payment settlement.

RekGo is an Indonesian escrow workflow prototype: Laravel web dashboard + MySQL/MariaDB + an independent Node.js WhatsApp bot (Baileys). The bot and web app share the **same business data via authenticated Laravel APIs**, not by giving the bot direct SQL write privileges.

## Technology and responsibilities

| Layer | Technology | Responsibility |
|---|---|---|
| Web / API | PHP >=8.2, Laravel 12, Blade | Authentication, users, escrow records, wallet/ledger, admin approval, audit |
| Database | MySQL / compatible MariaDB | Source of truth; migrations in `database/migrations` |
| WhatsApp | Node.js >=20, Baileys, Pino | Group commands, WhatsApp session, calls authenticated API |
| Bot process | PM2 | Keep Node process running, restart/log management |
| Merchant bridge | Node.js (optional) | GoPay merchant integration; proprietary SDK is not included |
| Edge | Nginx + PHP-FPM + TLS | Public HTTPS for Laravel; internal bot control is loopback only |

**Do not run multiple bot processes against the same Baileys auth directory.**

## Architecture

```text
User / Admin browser
       |
   HTTPS / Nginx
       |
  Laravel web/API ────────────── MySQL/MariaDB
       ^                             ^
       | authenticated API           | Laravel owns all writes
       |
 Node.js RekGo bot (PM2)
       |
 Baileys / WhatsApp groups

Optional merchant bridge -> payment provider (NOT live-verified)
```

Bot API routes (defined in `routes/web.php`, under `/api/rekgo-bot`):
- `POST /account`: registered user profile, masked bank and wallet balances.
- `POST /escrows`: create an escrow **draft** idempotently using WhatsApp message request ID.
- `POST /done`: accept a buyer completion request **only after escrow status is paid**; does **not** disburse funds.

Each route requires `Authorization: Bearer <REKGO_BOT_API_KEY>`; the same >=32-character secret must be configured in Laravel and the bot. Protect this secret and rotate if exposed. Apply request throttling and review audit logs.

## Repository map

```text
app/Http/Controllers/   Laravel web and API controllers
app/Models/             Eloquent data models
app/Services/           Wallet and business services
bootstrap/              Laravel bootstrap
config/                 Application and service config
database/migrations/    Database schema changes
database/seeders/       Optional seed data (avoid demo seeds in production)
resources/views/        Blade UI
resources/css/          UI styles
routes/web.php          Web and bot API endpoints
routes/console.php      Scheduler commands (if configured)
bot-rekgo/
  index.js              WhatsApp socket, API bridge, message dispatch
  plugins/commands.js   Command parser, menu, help
  package.json          Node dependencies
  ecosystem.config.js   PM2 single-process definition
  auth/                 Private WhatsApp session (never commit)
services/gopay-bridge/  Optional private merchant bridge
storage/                Private Laravel runtime files
tests/                  Laravel test suite
```

## Installation: Debian 12 (new VPS)

1. Configure DNS for your domain to point at VPS. Open only SSH, HTTP and HTTPS; do **not** expose MySQL or bot control port 4320 to the Internet.
2. Install system dependencies:

```bash
sudo apt update
sudo apt install -y git curl unzip nginx mariadb-server composer \
  php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath
php -v
node -v    # install supported Node >=20 if not present
```

3. Clone and install PHP dependencies:

```bash
sudo mkdir -p /rekgo
sudo chown "$USER":"$USER" /rekgo
git clone https://github.com/scardbypass/Rekgo.id.git /rekgo
cd /rekgo
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

4. Create an **empty** MySQL database and dedicated least-privilege user. Example for a new install (replace placeholders; do not paste real passwords into chat):

```sql
CREATE DATABASE rekgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rekgo_app'@'localhost' IDENTIFIED BY 'CHANGE_TO_STRONG_UNIQUE_PASSWORD';
GRANT ALL PRIVILEGES ON rekgo.* TO 'rekgo_app'@'localhost';
FLUSH PRIVILEGES;
```

5. Edit `/rekgo/.env`:

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
DB_PASSWORD=CHANGE_ME
REKGO_BOT_API_KEY=GENERATE_A_NEW_RANDOM_SECRET_MIN_32_CHARS
GOPAY_LIVE_VERIFIED=false
```

For a **new** installation only, run `php artisan key:generate`. **Never regenerate an existing production APP_KEY**: previously encrypted data/cookies may become unreadable. Preserve existing `.env` when upgrading.

```bash
cd /rekgo
php artisan key:generate   # NEW installation ONLY
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan route:list
php artisan migrate:status
```

6. Configure Nginx: domain `rekgo.my.id`, document root **`/rekgo/public`**, index `index.php`, PHP-FPM Unix socket matching the installed PHP version, Laravel `try_files $uri $uri/ /index.php?$query_string`. Configure TLS with your certificate/ACME provider. PHP-FPM must have write access to `storage` and `bootstrap/cache` only; never serve the project root. Test `sudo nginx -t` before reload.

7. Scheduler (for Laravel jobs requiring it):

```cron
* * * * * cd /rekgo && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Do not enable live GoPay/QRIS settlement before provider integration, journal verification, amount/reference matching, duplicate/late-payment handling, reconciliation and audit have been tested.

## Install independent WhatsApp bot

```bash
cd /rekgo/bot-rekgo
npm install
```

Create `/rekgo/bot-rekgo/.env` (do not commit):

```dotenv
REKGO_API_URL=https://rekgo.my.id
REKGO_BOT_API_KEY=EXACT_SAME_SECRET_AS_LARAVEL
REKGO_AUTH_DIR=./auth
REKGO_BOT_CONTROL_PORT=4320
REKGO_PAIR_PHONE=
OWNER_NUMBER=62XXXXXXXXXXX
```

- `REKGO_API_URL` must be the **base URL**, not `/api`.
- `REKGO_PAIR_PHONE` optional, format `62...`; leaving blank avoids automatic pairing attempts.
- `OWNER_NUMBER` is **reserved for future owner authorization**; it is not enforced by the current code.
- Do not put DB credentials in bot `.env`; all financial operations must pass Laravel validation.
- Ensure `bot-rekgo/auth/` is private and excluded from Git.

Start one PM2 process (ecosystem file assumes deployment path `/rekgo/bot-rekgo`):

```bash
npm install -g pm2
cd /rekgo/bot-rekgo
node --check index.js
node --check plugins/commands.js
pm2 start ecosystem.config.js
pm2 save
pm2 startup
pm2 status
pm2 logs rekgo-bot --lines 50
```

PM2 does not compile/build JavaScript; it runs the Node entrypoint. On upgrade, `pm2 restart rekgo-bot --update-env`. Execute any command printed by `pm2 startup`.

**WhatsApp pairing:** Baileys uses a private session in `auth/`. The bot exposes a Bearer-protected loopback control service at `127.0.0.1:4320`; admin UI may request a code, but pairing is **not verified operational**. HTTP 405 from WhatsApp is an upstream registration failure, not evidence that the Laravel database is broken. Check dependency compatibility and logs; back up auth before any session reset. Do not spam repeated pairing requests.

## WhatsApp commands — currently implemented

| Command | Who / where | Current behavior |
|---|---|---|
| `/menu`, `/start` | Registered group participant | Sends menu in DM |
| `/help`, `bantuan` | Registered group participant | Sends usage guide in DM |
| `akun`, `/akun` | Registered group participant | Sends account information in DM |
| `/rekgo 10000` | Registered seller, group | Creates pending escrow draft; **no verified payment instructions** |
| `rekber done` | Registered buyer, group | Requests admin review only when escrow is paid; **no payout** |

Account and menu commands currently trigger **from groups** (responses delivered by DM); private-chat command handling is not implemented. Registration is checked through Laravel. The bot does not currently enforce an owner command permission system. Plugin registry is a foundation; separate modules for status/history/disputes/admin are not implemented.

## Intended escrow workflow vs current implementation

1. Seller and buyer register on website using matching WhatsApp numbers.
2. Seller creates a group with buyer and RekGo bot.
3. Seller sends `/rekgo <amount>`. Laravel validates seller, fee and request id; creates **pending** escrow.
4. **Not yet complete:** provider-verified payment instruction, real settlement detection, and buyer/seller-specific confirmations.
5. After an escrow is marked `paid` by a trusted, verified process, buyer can send `rekber done`.
6. Laravel records an audit event for admin review. **No automatic fund release**.
7. Admin working hours are 09:00–20:00 WIB; outside hours follow next business window (operational policy, not a guaranteed implemented scheduler).

## Troubleshooting

| Symptom | Check |
|---|---|
| Laravel HTTP 500 | `tail -n 100 /rekgo/storage/logs/laravel.log`; PHP-FPM logs; migration status |
| Bot PM2 stopped | `pm2 logs rekgo-bot --lines 100`; Node version, missing `.env` |
| Bot HTTP 401 | Laravel and bot `REKGO_BOT_API_KEY` mismatch; clear Laravel config cache |
| Bot HTTP 503 | Socket offline; check `pm2 logs` and WhatsApp session |
| WhatsApp 405 | WhatsApp/Baileys registration or protocol problem; not solved by PM2 alone |
| `/akun` no reply | Group-only listener, number format, account registration, API response, DM privacy |
| Payment not marked paid | Expected until a verified settlement adapter is enabled/tested |

Useful diagnostics:

```bash
cd /rekgo
php artisan optimize:clear
php artisan migrate:status
php artisan test
cd /rekgo/bot-rekgo
node --check index.js
node --check plugins/commands.js
pm2 logs rekgo-bot --lines 50
```

Never share raw `.env`, authentication keys, database passwords, pairing codes or WhatsApp auth files. Rotate any secrets exposed in screenshots or logs; do not rotate `APP_KEY` without a deliberate data migration and recovery plan.

## Deployment update / rollback

```bash
cd /rekgo
git status --short
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
cd /rekgo/bot-rekgo
npm install
node --check index.js
pm2 restart rekgo-bot --update-env
```

Back up the DB and private state before deploying. Migration rollback may be destructive; use a tested database restore plan rather than blindly running `migrate:rollback` on live funds.

## Quality / release gates

- Run PHP lint and `php artisan test` against an isolated test database.
- Run `node --check` for all bot JS modules; add unit tests for command parsing and API failure paths.
- Test login, permissions, seller/buyer identity, duplicate WhatsApp events, concurrency and audit integrity.
- Test Baileys pairing with a test number, reconnect behavior, session persistence and privacy.
- Test reconciliation against merchant sandbox, late/duplicate callbacks, wrong amounts, refunds, and admin review.
- Test backups, restore, 2FA/admin hardening, monitoring and incident response.
- Obtain legal/payment provider approval before accepting real customer escrow funds.

**Verification note:** This guide documents code reviewed in GitHub; it is not evidence of a successful VPS deployment or end-to-end test.
