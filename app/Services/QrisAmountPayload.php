<?php
namespace App\Services;

use RuntimeException;

/** EMV TLV QRIS amount overlay for controlled merchant compatibility tests only. */
final class QrisAmountPayload
{
    public function withAmount(string $payload, int $rupiah): string
    {
        if ($rupiah < 1000 || $rupiah > 999999999) {
            throw new RuntimeException('Nominal uji tidak valid.');
        }
        if (!preg_match('/6304([0-9A-Fa-f]{4})$/', $payload)) {
            throw new RuntimeException('CRC QRIS tidak ditemukan.');
        }
        $body = substr($payload, 0, -8);
        $fields = [];
        $pos = 0;
        while ($pos < strlen($body)) {
            if ($pos + 4 > strlen($body)) throw new RuntimeException('TLV QRIS terpotong.');
            $tag = substr($body, $pos, 2);
            $length = substr($body, $pos + 2, 2);
            if (!ctype_digit($tag) || !ctype_digit($length)) throw new RuntimeException('TLV QRIS tidak valid.');
            $size = (int) $length;
            $pos += 4;
            if ($pos + $size > strlen($body)) throw new RuntimeException('Panjang TLV QRIS tidak valid.');
            $value = substr($body, $pos, $size);
            $pos += $size;
            if ($tag === '54') continue;
            if ($tag === '01') $value = '12'; // Point of initiation: dynamic
            $fields[] = [$tag, $value];
        }
        $result = '';
        $inserted = false;
        foreach ($fields as [$tag, $value]) {
            if (!$inserted && (int)$tag > 54) {
                $result .= '540' . strlen((string)$rupiah) . $rupiah;
                $inserted = true;
            }
            $result .= $tag . str_pad((string)strlen($value), 2, '0', STR_PAD_LEFT) . $value;
        }
        if (!$inserted) $result .= '540' . strlen((string)$rupiah) . $rupiah;
        $result .= '6304';
        $crc = 0xFFFF;
        foreach (unpack('C*', $result) as $byte) {
            $crc ^= $byte << 8;
            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) & 0xFFFF : ($crc << 1) & 0xFFFF;
            }
        }
        return $result . strtoupper(sprintf('%04X', $crc));
    }
}
