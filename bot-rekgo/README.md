# RekGo WhatsApp Bot (dedicated, separate from SUPER-BOT)

## Status
Initial integration scaffold only. **Do not accept real money through this bot yet.**
The `/rekgo 100000` command creates a pending escrow record on the website. It **does not** generate a verified payment link or QRIS and cannot confirm funds.
`rekber done` only submits an audit request when the escrow is already in `paid` status; it never releases money.

## Install
1. Deploy Laravel changes and run `php artisan migrate --force`.
2. Generate a unique random 32+ character secret. Put it in Laravel `.env` as `REKGO_BOT_API_KEY=...` and run `php artisan config:clear`. Never commit secrets.
3. In `bot-rekgo`, run `npm install`, copy `.env.example` to `.env`, set the same key and website URL.
4. Run `npm start`, scan the WhatsApp QR with a **dedicated RekGo number**. Store the auth directory securely and exclude it from Git.
5. Add the bot to a WhatsApp group. Seller must be a registered RekGo account with a matching WhatsApp phone number. Send `/rekgo 100000`.
6. Verify the pending escrow in the admin dashboard. **Do not transfer funds until a verified payment gateway and buyer identity verification are implemented and tested.**

## API
- `POST /api/rekgo-bot/escrows` Bearer token, `request_id`, `group_jid`, `seller_jid`, `seller_phone`, `amount`.
- `POST /api/rekgo-bot/done` Bearer token, `group_jid`, `buyer_jid`. No settlement.
API key is shared between Laravel and bot; treat it like a password.

## Before production
Implement a verified gateway payment flow and signed provider webhooks, durable payment idempotency, WhatsApp group membership / buyer verification, admin settlement review, payment expiry and cancellation, concurrency tests, and secure session management. Current integration is a prototype, not a production escrow/payment processor.
