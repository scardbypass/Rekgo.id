@extends('layout')
@section('content')
<div class="rg-page-heading"><a href="/admin/page/users">← Semua pengguna</a><h1>{{ $member->name }}</h1><p>{{ '@'.$member->username }} · {{ $member->role }}</p></div>
<div class="grid rg-settings-grid"><section class="glass"><h2>Informasi pengguna</h2><p><strong>Email</strong><br>{{ $member->email }}</p><p><strong>Telepon</strong><br>{{ $member->phone ?: 'Tidak tersedia' }}</p><p><strong>Bergabung</strong><br>{{ $member->created_at?->format('d M Y H:i') }}</p><p><strong>Bank</strong><br>{{ $member->bank_code ?: 'Belum diatur' }} · {{ $member->bank_account ? '••••'.substr($member->bank_account,-4) : 'Tidak tersedia' }}</p><span class="pill">{{ $member->bank_verified_at ? 'Bank terverifikasi' : 'Bank belum terverifikasi' }}</span></section><section class="glass"><h2>Ringkasan saldo</h2><div class="rg-record"><span>Tersedia</span><strong>Rp{{ number_format($balance->available??0,0,',','.') }}</strong></div><div class="rg-record"><span>Tertunda</span><strong>Rp{{ number_format($balance->pending??0,0,',','.') }}</strong></div><div class="rg-record"><span>Terkunci</span><strong>Rp{{ number_format($balance->locked??0,0,',','.') }}</strong></div></section></div>
@if(auth()->user()->role === 'super_admin')
<section class="glass" style="margin-top:16px">
<h2>Penyesuaian Saldo Manual</h2>
<p class="muted small">Hanya Super Admin. Setiap perubahan dicatat dalam ledger dan audit. Jangan gunakan untuk menandai pembayaran QRIS yang belum terverifikasi.</p>
<form method="post" action="/admin/users/{{ $member->id }}/balance-adjust" onsubmit="return confirm('Yakin ingin menyesuaikan saldo pengguna ini? Tindakan akan dicatat dalam audit.')">
@csrf
<label for="balance-operation">Jenis penyesuaian</label>
<select id="balance-operation" name="operation" required>
<option value="add" {{ old('operation')==='add'?'selected':'' }}>+ Tambah saldo</option>
<option value="subtract" {{ old('operation')==='subtract'?'selected':'' }}>− Kurangi saldo</option>
</select>
<label for="balance-amount">Nominal (Rp)</label>
<input id="balance-amount" name="amount" type="number" min="1" max="100000000" step="1" required placeholder="Contoh: 10000" value="{{ old('amount') }}">
<label for="balance-reason">Alasan penyesuaian (wajib)</label>
<textarea id="balance-reason" name="reason" rows="3" minlength="10" maxlength="500" required placeholder="Contoh: Koreksi saldo berdasarkan mutasi merchant yang diverifikasi">{{ old('reason') }}</textarea>
<label for="balance-confirmation">Konfirmasi: ketik ADJUST</label>
<input id="balance-confirmation" name="confirmation" type="text" required pattern="ADJUST" autocomplete="off" placeholder="ADJUST">
<button type="submit">Simpan Penyesuaian Saldo</button>
</form>
</section>
@endif
<section class="glass" style="margin-top:16px"><h2>QRIS terbaru</h2><div class="rg-records">@forelse($payments as $p)<div class="rg-record"><span>#{{ $p->id }} · {{ $p->status }}</span><strong>Rp{{ number_format($p->gross,0,',','.') }}</strong></div>@empty<p class="muted">Belum ada pembayaran.</p>@endforelse</div></section>
<section class="glass" style="margin-top:16px"><h2>Pencairan terbaru</h2><div class="rg-records">@forelse($withdrawals as $wd)<div class="rg-record"><span>#{{ $wd->id }} · {{ $wd->status }}</span><strong>Rp{{ number_format($wd->amount,0,',','.') }}</strong></div>@empty<p class="muted">Belum ada pencairan.</p>@endforelse</div></section>
@endsection