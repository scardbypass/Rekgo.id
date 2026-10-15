@extends('layout')
@section('content')
<div class="rg-page-heading"><a href="/admin/page/users">← Semua pengguna</a><h1>{{ $member->name }}</h1><p>{{ '@'.$member->username }} · {{ $member->role }}</p></div>
<div class="grid rg-settings-grid"><section class="glass"><h2>Informasi pengguna</h2><p><strong>Email</strong><br>{{ $member->email }}</p><p><strong>Telepon</strong><br>{{ $member->phone ?: 'Tidak tersedia' }}</p><p><strong>Bergabung</strong><br>{{ $member->created_at?->format('d M Y H:i') }}</p><p><strong>Bank</strong><br>{{ $member->bank_code ?: 'Belum diatur' }} · {{ $member->bank_account ? '••••'.substr($member->bank_account,-4) : 'Tidak tersedia' }}</p><span class="pill">{{ $member->bank_verified_at ? 'Bank terverifikasi' : 'Bank belum terverifikasi' }}</span></section><section class="glass"><h2>Ringkasan saldo</h2><div class="rg-record"><span>Tersedia</span><strong>Rp{{ number_format($balance->available??0,0,',','.') }}</strong></div><div class="rg-record"><span>Tertunda</span><strong>Rp{{ number_format($balance->pending??0,0,',','.') }}</strong></div><div class="rg-record"><span>Terkunci</span><strong>Rp{{ number_format($balance->locked??0,0,',','.') }}</strong></div></section></div>
@if(auth()->user()->role === 'super_admin')
<section class="glass" style="margin-top:16px">
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
<div><h2 style="margin-bottom:4px">Kelola Saldo</h2><p class="muted small" style="margin:0">Saldo tersedia: <strong>Rp{{ number_format($balance->available??0,0,',','.') }}</strong></p></div>
<span class="pill">Super Admin</span>
</div>
<p class="muted small">Pilih tindakan untuk membuka formulir. Semua perubahan masuk riwayat audit.</p>
<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:14px 0">
<button type="button" id="rg-balance-add" onclick="rgBalanceAction('add')" style="width:100%;border-radius:14px">＋ Tambah Saldo</button>
<button type="button" id="rg-balance-subtract" onclick="rgBalanceAction('subtract')" style="width:100%;border-radius:14px;background:#be3545;color:#fff">－ Kurangi Saldo</button>
</div>
<div id="rg-balance-panel" style="display:none;border-top:1px solid var(--line);padding-top:16px">
<h3 id="rg-balance-title">Tambah Saldo</h3>
<form method="post" action="/admin/users/{{ $member->id }}/balance-adjust" onsubmit="return confirm('Konfirmasi penyesuaian saldo untuk {{ addslashes($member->username) }} sebesar Rp'+Number(document.getElementById('balance-amount').value||0).toLocaleString('id-ID')+'?')">
@csrf
<input type="hidden" id="balance-operation" name="operation" value="{{ old('operation','add') }}">
<label for="balance-amount">Nominal (Rp)</label>
<input id="balance-amount" name="amount" type="number" min="1" max="100000000" step="1" required placeholder="Contoh: 10000" value="{{ old('amount') }}">
<label for="balance-reason">Alasan penyesuaian</label>
<textarea id="balance-reason" name="reason" rows="3" minlength="10" maxlength="500" required placeholder="Contoh: Koreksi saldo setelah verifikasi mutasi merchant">{{ old('reason') }}</textarea>
<button type="submit" id="rg-balance-submit" style="width:100%">Konfirmasi Tambah Saldo</button>
<button type="button" onclick="document.getElementById('rg-balance-panel').style.display='none'" style="width:100%;margin-top:8px;background:transparent;border:1px solid var(--line)">Batal</button>
</form>
</div>
</section>
<script>
function rgBalanceAction(action){
 const subtract=action==='subtract';
 document.getElementById('balance-operation').value=action;
 document.getElementById('rg-balance-title').textContent=subtract?'Kurangi Saldo':'Tambah Saldo';
 document.getElementById('rg-balance-submit').textContent=subtract?'Konfirmasi Kurangi Saldo':'Konfirmasi Tambah Saldo';
 document.getElementById('rg-balance-panel').style.display='block';
 document.getElementById('balance-amount').focus();
}
@if(old('amount'))
document.addEventListener('DOMContentLoaded',function(){rgBalanceAction(@json(old('operation','add')))});
@endif
</script>
@endif
<section class="glass" style="margin-top:16px"><h2>QRIS terbaru</h2><div class="rg-records">@forelse($payments as $p)<div class="rg-record"><span>#{{ $p->id }} · {{ $p->status }}</span><strong>Rp{{ number_format($p->gross,0,',','.') }}</strong></div>@empty<p class="muted">Belum ada pembayaran.</p>@endforelse</div></section>
<section class="glass" style="margin-top:16px"><h2>Pencairan terbaru</h2><div class="rg-records">@forelse($withdrawals as $wd)<div class="rg-record"><span>#{{ $wd->id }} · {{ $wd->status }}</span><strong>Rp{{ number_format($wd->amount,0,',','.') }}</strong></div>@empty<p class="muted">Belum ada pencairan.</p>@endforelse</div></section>
@endsection