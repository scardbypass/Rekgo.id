<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ManualQrisController
{
    public function index(Request $request)
    {
        abort_unless(in_array($request->user()?->role, ['super_admin','finance'], true), 403);
        return view('admin-manual-qris', [
            'payments' => Payment::where('provider', 'qris_manual')->latest()->paginate(30),
        ]);
    }

    public function decide(Request $request, Payment $payment, Wallet $wallet)
    {
        abort_unless(in_array($request->user()?->role, ['super_admin','finance'], true), 403);
        $data = $request->validate([
            'decision' => 'required|in:approve,cancel',
            'reference' => 'required_if:decision,approve|nullable|string|regex:/^[A-Za-z0-9_.:\-]{8,150}$/',
            'note' => 'nullable|string|max:500',
        ]);
        abort_unless($payment->provider === 'qris_manual', 404);
        DB::transaction(function () use ($payment, $wallet, $request, $data) {
            DB::table('payment_provider_settings')->where('provider', 'gopay_marc')->lockForUpdate()->first();
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'pending', 409, 'Tagihan sudah diproses.');
            if ($data['decision'] === 'approve') {
                $ref = trim($data['reference']);
                // Serialize claims and require unique merchant transfer reference.
                $other = Payment::where('provider', 'qris_manual')
                    ->where('provider_ref', $ref)->whereKeyNot($locked->id)->exists();
                abort_if($other, 409, 'Referensi mutasi sudah digunakan.');
                $balance = $wallet->balance((int) $locked->user_id);
                $wallet->move($balance, 'available', (int) $locked->net, 'payment', $locked->id, 'QRIS manual verified by admin');
                $locked->provider_ref = $ref;
                $locked->provider_status = 'admin_verified';
                $locked->status = 'success';
                $locked->paid_at = now();
            } else {
                $locked->status = 'cancelled';
                $locked->provider_status = 'admin_cancelled';
            }
            $locked->next_check_at = null;
            $locked->last_checked_at = now();
            $locked->save();
            $wallet->audit((int) $request->user()->id, 'qris.manual_'.$data['decision'], 'payment', $locked->id, ['note' => $data['note'] ?? null, 'reference' => $data['decision'] === 'approve' ? $locked->provider_ref : null]);
        }, 3);
        return back()->with('ok', 'Keputusan admin berhasil disimpan.');
    }
}
