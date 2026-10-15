<?php
namespace App\Http\Controllers;

use App\Services\StaticQris;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;

final class GoPayLiveTestController
{
    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->user()?->role === 'super_admin', 403);
    }

    public function show(Request $request, StaticQris $qris)
    {
        $this->authorizeOwner($request);
        $ready = true;
        try { $qris->payload(); } catch (\Throwable $e) { $ready = false; }
        return view('admin-gopay-live-test', ['ready' => $ready]);
    }

    public function check(Request $request, \App\Services\GopayBridge $bridge)
    {
        $this->authorizeOwner($request);
        $data = $request->validate(['reference' => ['required', 'string', 'regex:/^[A-Za-z0-9_.:-]{8,150}$/']]);
        try {
            $result = $bridge->call('status', ['reference' => $data['reference']]);
        } catch (\Throwable $e) {
            return back()->with('check_result', 'Belum dapat diverifikasi: koneksi jurnal/status GoPay belum tersedia atau referensi tidak didukung.');
        }
        $entry = is_array($result['data'] ?? null) ? $result['data'] : $result;
        $status = strtolower((string)($entry['transaction_status'] ?? $entry['status'] ?? ''));
        $ref = (string)($entry['transaction_id'] ?? $entry['transactionId'] ?? $entry['id'] ?? '');
        $amount = $entry['gross_amount'] ?? $entry['grossAmount'] ?? $entry['amount'] ?? null;
        $valid = $ref !== '' && hash_equals($data['reference'], $ref)
            && \App\Services\ExactIdrAmount::matches($amount, 1001)
            && in_array($status, ['settlement', 'capture', 'paid', 'success'], true);
        return back()->with('check_result', $valid
            ? 'Status provider menyatakan pembayaran Rp1.001 berhasil untuk referensi tersebut. Periksa mutasi merchant; saldo RekGo tidak ditambah.'
            : 'Pembayaran BELUM terverifikasi. Status, referensi, atau nominal tidak cocok. Periksa mutasi merchant.');
    }

    public function image(Request $request, StaticQris $qris, \App\Services\QrisAmountPayload $amountPayload)
    {
        $this->authorizeOwner($request);
        try { $payload = $amountPayload->withAmount($qris->payload(), 1001); }
        catch (\Throwable $e) { abort(404, 'QRIS merchant belum tersedia.'); }
        $svg = (new SvgWriter())->write(new QrCode(data: $payload, size: 360, margin: 16))->getString();
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
