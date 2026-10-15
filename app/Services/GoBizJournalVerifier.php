<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

final class GoBizJournalVerifier
{
    public function candidate(int $paymentId, int $grossIdr, array $entries, string $merchantId): array
    {
        $matches = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) continue;
            $tx = $entry['metadata']['transaction'] ?? null;
            if (!is_array($tx)) continue;
            $ref = $entry['id'] ?? $entry['reference_id'] ?? $tx['id'] ?? null;
            if (!is_string($ref) || $ref === '') continue;
            if ((string) ($tx['merchant_id'] ?? '') !== $merchantId) continue;
            if (!is_numeric($tx['gross_amount'] ?? null)) continue;
            if ((int) $tx['gross_amount'] !== $grossIdr * 100) continue;
            $matches[$ref] = $ref;
        }
        if (count($matches) !== 1) return ['matched' => false, 'reason' => 'missing_or_ambiguous'];
        $ref = reset($matches);
        if (DB::table('payments')->where('provider_ref', $ref)->where('id', '!=', $paymentId)->exists()) {
            return ['matched' => false, 'reason' => 'reference_already_used'];
        }
        return ['matched' => true, 'reference' => $ref, 'requires_atomic_settlement' => true];
    }
}
