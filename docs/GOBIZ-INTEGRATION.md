# GoBiz integration boundary

RekGo is a public repository. Keep the licensed CeirGo GoBiz module, merchant credentials, access tokens and refresh tokens on a private VPS service. Never commit them here.

The CeirGo module provides merchant authentication, token refresh and journal searches. It does not provide the create/status/cancel API currently expected by RekGo's GopayPayments service. Do not enable live QRIS by changing the feature flag alone.

Implementation checklist:

1. Build a private bridge to return authenticated, normalized journal records and merchant identity.
2. Validate the QRIS payload and merchant ownership before displaying an invoice.
3. Match incoming transactions using amount, merchant, timestamp and unique provider reference; ambiguous matches require manual review.
4. Enforce a unique database claim on the provider reference, with atomic payment settlement and wallet credit.
5. Verify expiry and late payments, test duplicate notifications, and reconcile provider totals against ledger entries.
6. Only enable live acceptance after controlled end-to-end tests on the actual merchant account.

Do not log credentials, bearer tokens or complete QRIS secrets.
