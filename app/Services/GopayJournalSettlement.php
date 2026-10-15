<?php
namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Settlement jurnal GoPay. Hanya panggil dengan data yang telah diverifikasi
 * dari API merchant; tidak menerima status dari browser/user.
 */
final class GopayJournalSettlement
{
    public function __construct(private Wallet $wallet) {}

    public function settle(int $paymentId, array $entry): bool
    {
        $reference = $entry['reference'] ?? null;
        $merchant = $entry['merchant_id'] ?? null;
        $amount = $entry['amount'] ?? null;
        $currency = $entry['currency'] ?? null;
        $status = strtolower((string) ($entry['status'] ?? ''));
        $direction = strtolower((string) ($entry['direction'] ?? ''));
        $paidAt = $entry['paid_at'] ?? null;

        if (!is_string($reference) || !preg_match('/^[a-zA-Z0-9_.:-]{8,150}$/', $reference)
            || !is_string($merchant) || $merchant === ''
            || $currency !== 'IDR' || $direction !== 'incoming'
            || !in_array($status, ['success', 'paid', 'settlement'], true)
            || !is_string($paidAt)) {
            return false;
        }

        try {
            $timestamp = \Carbon\CarbonImmutable::parse($paidAt);
        } catch (\Throwable $e) {
            return false;
        }

        return DB::transaction(function () use ($paymentId, $reference, $merchant, $amount, $timestamp) {
            // Serialize settlements per merchant and reject references reused by another order.
            $setting = DB::table('payment_provider_settings')
                ->where('provider', 'gopay_marc')->lockForUpdate()->first();
            if (!$setting || !$setting->enabled || !config('rekgo.gopay_live_verified')
                || !$setting->merchant_id || !hash_equals((string)$setting->merchant_id, $merchant)) {
                return false;
            }

            $payment = Payment::whereKey($paymentId)->lockForUpdate()->first();
            if (!$payment || $payment->status !== 'pending' || $payment->provider !== 'gopay_marc'
                || !ExactIdrAmount::matches($amount, (int)$payment->gross)
                || $timestamp->lt($payment->created_at)
                || $timestamp->gt(now()->addMinutes(5))
                || ($payment->expires_at && $timestamp->gt($payment->expires_at))) {
                return false;
            }

            $duplicate = Payment::where('provider', 'gopay_marc')
                ->where('provider_ref', $reference)->where('id', '!=', $paymentId)->exists();
            if ($duplicate || ($payment->provider_ref && $payment->provider_ref !== $reference)) {
                Log::warning('Duplicate or conflicting GoPay journal reference', ['payment_id' => $paymentId]);
                return false;
            }

            $balance = $this->wallet->balance((int)$payment->user_id);
            $this->wallet->move($balance, 'available', (int)$payment->net, 'payment', $payment->id, 'GoPay journal confirmed');
            $payment->provider_ref = $reference;
            $payment->provider_status = 'settlement';
            $payment->status = 'success';
            $payment->paid_at = $timestamp;
            $payment->last_checked_at = now();
            $payment->next_check_at = null;
            $payment->save();
            $this->wallet->audit(null, 'qris.journal_confirmed', 'payment', $payment->id);
            return true;
        }, 3);
    }
}
