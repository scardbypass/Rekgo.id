# GoPay Merchant (GMA) bridge — integration stage

This service provides an authenticated **localhost-only** interface to the user's separately licensed `gma.js` SDK. The proprietary SDK is **not included** in this distribution. Verify your license and merchant authorization before installation. No payment processing is enabled by default.

1. Install Node.js 20+ on VPS. Copy an authorized `gma.js` to `licensed-sdk/gma.js` (or set `GMA_SDK_PATH`). Do not commit it or the tokens to Git.
2. Generate a random secret: `openssl rand -hex 32`; set the **same** value in `BRIDGE_KEY` and Laravel `GOPAY_BRIDGE_KEY`. Set `GMA_OUTLET_ID` from your merchant account.
3. Set environment variables (via systemd/PM2 env, not by automatically sourcing untrusted files). Run `node server.cjs` from this directory. It binds **127.0.0.1:4319**, not a public interface. Restrict access with OS firewall and separate service accounts.
4. Complete merchant OTP authentication from a **secure admin-only workflow** before enabling live operations. The bridge exposes `login-request` and `login-verify` but this release intentionally does not provide an admin UI for handling OTP; do not expose this interface to users.
5. Laravel can call `App\\Services\\GopayBridge::call('create',['amount'=>100000,'label'=>'RG-123'])` and `call('status',['reference'=>'provider-reference'])`. Do not assume response keys; verify with provider's current documented contract.

**Not production-ready:** The existing `/qris` endpoint still creates an internal draft only. No automatic payment confirmation, settlement, webhook validation, expiry cancellation, ledger posting, reconciliation, fee-payer compliance enforcement, or live admin activation is implemented. Do not change a payment to Success from unverified response content. The provider may require approved merchant access; dynamic QRIS and fee policies must comply with provider/BI rules. Avoid putting raw provider tokens in logs. Confirm the SDK's `onTokenChange` signature before relying on automatic refresh persistence.
