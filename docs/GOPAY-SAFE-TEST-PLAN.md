# GoPay Merchant — persiapan pengujian aman RekGo V4

## Prinsip

Merchant aktif **bukan** sandbox. Jangan membuat QRIS yang dapat dibayar, mengirim transaksi, menekan tombol transfer, atau memakai nominal Rp1 untuk uji coba tanpa persetujuan penyedia. Tidak ada kredensial/OTP/token di GitHub, chat, atau log.

## Gate 0 — lingkungan terisolasi (tanpa transaksi)

1. Buat database MySQL **khusus pengujian** dan akun uji tanpa saldo nyata. Jangan salin database pelanggan.
2. Di `.env`, set `APP_ENV=local`, `APP_DEBUG=false`, `GOPAY_LIVE_VERIFIED=false`, `REKGO_PROOF_CLEANUP_ENABLED=false`. Jangan mengaktifkan PG di Admin.
3. Jalankan `composer install`, `php artisan migrate`, `php artisan route:list`, `php artisan test`.
4. Jalankan GoPay bridge di localhost saja. Pastikan `BRIDGE_KEY` panjang acak >=32 karakter, URL Laravel ke `http://127.0.0.1:4319`, port 4319 tidak terbuka dari Internet.
5. Uji health proses bridge tanpa memanggil aksi create/status/cancel ke akun merchant aktif. Jangan merekam token/OTP di output.

## Gate 1 — mock/stub provider, tanpa uang

Uji create/status/cancel dengan provider mock di jaringan lokal. Kasus minimum:
- nominal persis sama dan referensi sama;
- nominal berbeda, pecahan, kosong, string notasi ilmiah;
- referensi berbeda, hilang, duplicate response;
- status unknown, pending, failed, paid, cancelled, expired;
- timeout, retry, dua worker bersamaan;
- expiry lalu late settlement, cancellation race, provider create sukses tapi respons timeout;
- setiap payment maksimal satu ledger credit, invariant saldo tetap konsisten;
- QRIS provider payload hanya ditampilkan kepada pemilik dan tidak setelah expired.

**Kriteria lulus:** semua kasus negatif tidak menambah saldo; hanya transaksi terverifikasi berhak diproses sekali.

## Gate 2 — merchant provider test

Hanya setelah penyedia mengonfirmasi sandbox/test outlet atau skenario resmi **non-finansial**, lakukan tes autentikasi dan respons provider. Jangan menebak format SDK. Validasi kontrak SDK dengan dokumentasi yang berlaku, termasuk token refresh, idempotency, signature dan webhook.

## Gate 3 — produksi

Butuh otorisasi merchant, uji settlement/refund, bank reconciliation, kepatuhan biaya QRIS, audit keamanan, 2FA, dispute, legal hold, monitoring dan rollback. Jangan nyalakan PG hanya karena tes unit lulus.

## Cara jalankan tes validasi nominal

```bash
composer install
./vendor/bin/phpunit tests/Unit/ExactIdrAmountTest.php
```

Tes di atas **belum dieksekusi di CI/server** saat file ini ditambahkan. Status live: OFF.
