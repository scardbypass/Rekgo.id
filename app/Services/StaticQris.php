<?php
namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * QRIS statis adalah payload merchant, bukan QRIS dinamis.
 * Nilai tagihan harus ditampilkan terpisah: QR ini TIDAK menyematkan nominal unik.
 */
final class StaticQris
{
    public function payload(): string
    {
        $disk = Storage::disk('local');
        $path = 'private/gopay-qris.enc';
        if (!$disk->exists($path)) {
            throw new RuntimeException('QRIS statis merchant belum disimpan.');
        }
        $payload = trim(Crypt::decryptString($disk->get($path)));
        if (!str_starts_with($payload, '000201') || !preg_match('/6304[0-9A-Fa-f]{4}$/', $payload)) {
            throw new RuntimeException('Payload QRIS statis tidak valid.');
        }
        return $payload;
    }

    /**
     * Alokasikan nominal unik hanya di antara tagihan pending yang belum kedaluwarsa.
     * Wajib dipanggil di dalam transaksi database yang memegang kunci serialisasi.
     * Offset maksimum 999 rupiah; jangan mengklaim ini aman tanpa worker rekonsiliasi.
     */
    public function uniqueGross(int $base, array $reserved): int
    {
        if ($base < 1000 || $base > 999999000) {
            throw new RuntimeException('Nominal di luar batas.');
        }
        $used = array_fill_keys(array_map('intval', $reserved), true);
        for ($offset = 1; $offset <= 999; $offset++) {
            $gross = $base + $offset;
            if (!isset($used[$gross])) {
                return $gross;
            }
        }
        throw new RuntimeException('Nominal unik sedang penuh. Coba lagi nanti.');
    }
}
