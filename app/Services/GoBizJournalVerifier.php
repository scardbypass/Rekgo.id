<?php
namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Verify a journal search response from a trusted private GoBiz bridge.
 * This service deliberately does not settle payments: settlement must claim
 * the provider reference atomically and post exactly one ledger entry.
 */
final class GoBizJournalVerifier
{
    public function candidate(Payment $payment, array $response, string $merchantId): array
    {
        if ($payment->status !== 'pending' || !$payment->expires_at || !$payment->created_at
            || $merchantId === '' || !($response['authenticated'] ?? false)
            || !hash_equals($merchantId, (string) ($response['merchant_id'] ?? ''))
            || !($response['complete'] ?? false)) {
            return ['matched' => false, 'reason' => 'untrusted_or_incomplete_journal'];
        }

        $entries = $response['hits'] ?? null;
        if (!is_array($entries)) return ['matched' => false, 'reason' => 'invalid_journal'];
        $matches = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) continue;
            $tx = $entry['metadata']['transaction'] ?? null;
            if (!is_array($tx)) continue;
            $ref = $entry['id'] ?? $entry['reference_id'] ?? $tx['id'] ?? null;
            if (!is_string($ref) || !preg_match('/^[A-Za-z0-9_.:-]{8,150}$/', $ref)) continue;
            if (!hash_equals($merchantId, (string) ($tx['merchant_id'] ?? ''))) continue;
            $raw = $tx['gross_amount'] ?? null;
            if (!is_int($raw) && !(is_string($raw) && ctype_digit($raw))) continue;
            if ((string) $raw !== (string) ((int) $payment->gross * 100)) continue;
            $time = $tx['transaction_time'] ?? null;
            if (!is_string($time)) continue;
            try { $at = new \DateTimeImmutable($time); } catch (\Throwable $e) { continue; }
            if ($at < $payment->created_at->toDateTimeImmutable()
                || $at > $payment->expires_at->toDateTimeImmutable()) continue;
            $matches[$ref] = $ref;
        }

        if (count($matches) !== 1) return ['matched' => false, 'reason' => 'missing_or_ambiguous'];
        $ref = reset($matches);
        if (DB::table('payments')->where('provider_ref', $ref)->where('id', '!=', $payment->id)->exists()) {
            return ['matched' => false, 'reason' => 'reference_already_used'];
        }

        // The caller must also atomically claim this reference and settle the
        // wallet under database locks; this preliminary match is NOT settlement.
        return ['matched' => true, 'reference' => $ref, 'requires_atomic_settlement' => true];
    }
}
