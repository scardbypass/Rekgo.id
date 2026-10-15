@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin</p><h1>Verifikasi QRIS Manual</h1><p>Periksa mutasi merchant sebelum ACC. Jangan menyetujui berdasarkan screenshot pembeli saja.</p></div>
<section class="glass rg-compact-panel">
@forelse($payments as $p)
<div class="rg-record" style="display:block;padding:18px 0;border-bottom:1px solid var(--line)">
<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
 <div><strong>Tagihan #{{ $p->id }}</strong><p class="muted small">User #{{ $p->user_id }} • {{ $p->created_at?->format('d M Y H:i') }}</p></div>
 <div><strong>Rp{{ number_format($p->gross,0,',','.') }}</strong><p class="muted small">{{ strtoupper($p->status) }}</p></div>
</div>
@if($p->status==='pending')
<form action="{{ route('admin.manual-qris.decide',$p) }}" method="post" style="display:grid;gap:10px;margin-top:12px">@csrf
<label>Referensi mutasi merchant (wajib untuk ACC)</label>
<input name="reference" maxlength="150" placeholder="ID transaksi dari mutasi merchant">
<label>Catatan admin (opsional)</label><input name="note" maxlength="500" placeholder="Hasil pengecekan">
<div style="display:flex;gap:10px;flex-wrap:wrap">
<button type="submit" name="decision" value="approve" onclick="return confirm('Sudah cocokkan nominal, waktu, merchant, dan referensi mutasi? Saldo akan langsung masuk.')">ACC / Done</button>
<button type="submit" name="decision" value="cancel" class="btn secondary" onclick="return confirm('Batalkan tagihan ini?')">Batalkan</button>
</div>
</form>
@else
<p class="muted small">Referensi: {{ $p->provider_ref ?: '—' }}</p>
@endif
</div>
@empty
<p>Belum ada tagihan QRIS manual.</p>
@endforelse
{{ $payments->links() }}
</section>
@endsection
