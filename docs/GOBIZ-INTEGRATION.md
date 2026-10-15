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

## Mengisi akun merchant GoPay / GoBiz

Masuk sebagai Super Admin RekGo, lalu buka **Admin → Integrasi → GoPay**. Isi email merchant dan password pada formulir kredensial, kemudian simpan. Kredensial disimpan terenkripsi pada disk lokal Laravel, bukan di repository. Pastikan akun merchant benar-benar memiliki akses jurnal dan merchant ID yang sesuai. Untuk QRIS statis, masukkan QRIS Text merchant yang lengkap dan lolos pemeriksaan CRC. Jangan memasukkan token, password, atau QRIS sensitif ke commit Git.

Konfigurasi koneksi adapter melalui environment privat VPS:

- `GOPAY_BRIDGE_URL`: alamat service bridge internal (default `http://127.0.0.1:4319`).
- `GOPAY_BRIDGE_KEY`: secret Bearer acak minimal 32 karakter; sama di website dan bridge.
- `REKGO_BOT_API_KEY`: kunci khusus bot, **berbeda** dari bridge.
- `REKGO_GOPAY_LIVE_VERIFIED`: jangan diaktifkan sebelum end-to-end reconciliation berhasil.

Modul CeirGo yang dilampirkan menggunakan `GOPAY_EMAIL`, `GOPAY_PASSWORD`, `GOPAY_ACCESS_TOKEN`, `GOPAY_REFRESH_TOKEN`, dan `GOPAY_MERCHANT_ID` pada penyimpanan konfigurasi internalnya. Login dilakukan dengan password, lalu refresh token saat diperlukan; transaksi masuk dicari pada jurnal berdasarkan merchant, waktu dan nominal (nominal jurnal dalam satuan 1/100 rupiah).

**Perbedaan penting:** RekGo `GopayPayments` saat ini meminta bridge `create`, `status`, `cancel`; modul CeirGo hanya menyediakan login dan pencarian jurnal. Adapter tersebut belum bisa langsung menggantikan kontrak lama. Tidak aman untuk menganggap satu kecocokan nominal sebagai bukti pembayaran invoice tertentu. Jangan gunakan sistem ini untuk dana sungguhan sebelum identitas invoice unik, penguncian klaim jurnal secara atomik, dan verifikasi rekonsiliasi selesai.
