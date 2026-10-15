@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin Center</p><h1>WhatsApp Bot</h1><p>Kelola perangkat WhatsApp tanpa bercampur dengan pengaturan pembayaran.</p></div>
<p><a class="btn secondary" href="/admin/integrations">← Semua Integrasi</a></p>
<section class="glass"><h2>Tambah Bot</h2><p class="muted small">QR dan pairing baru dapat digunakan setelah bridge Baileys aktif.</p>
<form method="post" action="/admin/integrations/bots">@csrf
<label>Nama Bot</label><input name="label" required maxlength="100" placeholder="RekGo Utama">
<label>Nomor WhatsApp (62...)</label><input name="phone" required pattern="62[0-9]{8,13}" placeholder="6281234567890">
<label>Metode koneksi</label><select name="connection_method"><option value="qr">QR Code</option><option value="pair">Pairing Code</option></select>
<button type="submit">Daftarkan Bot</button></form></section>
<div class="gap"></div><section class="glass"><h2>Bot Terdaftar</h2>
@forelse($bots as $bot)
<div style="padding:16px 0;border-bottom:1px solid var(--line)">
<div class="row"><div><strong>{{ $bot->label }}</strong><p class="muted small">{{ $bot->phone }} · {{ $bot->connection_method }}</p></div><span class="pill">{{ $bot->status }}</span></div>
<p class="muted small">{{ $bot->enabled ? 'Aktif' : 'Nonaktif' }}</p>
<div class="row" style="gap:8px;flex-wrap:wrap">
<form method="post" action="/admin/integrations/bots/{{ $bot->id }}/toggle">@csrf<input type="hidden" name="enabled" value="{{ $bot->enabled ? 0 : 1 }}"><button type="submit" class="secondary">{{ $bot->enabled ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
@if($bot->status !== 'online')
<form method="post" action="/admin/integrations/bots/{{ $bot->id }}/delete" onsubmit="return confirm('Hapus bot ini dari RekGo? Pastikan sesi bridge WhatsApp sudah diputus.')">@csrf<button type="submit" class="secondary" style="color:#dc3545">Hapus Bot</button></form>
@else <span class="muted small">Putuskan koneksi bot sebelum menghapus.</span> @endif
</div></div>
@empty<p class="muted">Belum ada bot terdaftar.</p>@endforelse
</section>
@endsection