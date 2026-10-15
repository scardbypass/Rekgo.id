@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin Center</p><h1>Integrasi</h1><p>Pilih layanan yang ingin kamu kelola. Setiap layanan memiliki halaman pengaturan sendiri.</p></div>
<p><a class="btn secondary" href="/admin">← Dashboard Admin</a></p>
<div class="grid rg-admin-grid">
<a class="glass" href="/admin/integrations/bots"><span class="rg-icon">◉</span><h2>WhatsApp Bot</h2><p class="muted">Tambah, aktifkan, nonaktifkan, dan hapus bot.</p><span class="pill">{{ $bots->count() }} terdaftar</span></a>
<a class="glass" href="/admin/integrations/gopay"><span class="rg-icon">◇</span><h2>GoPay Merchant</h2><p class="muted">QRIS, pengaturan gateway, dan koneksi merchant.</p><span class="pill">{{ $pg && $pg->enabled ? 'Aktif' : 'Belum aktif' }}</span></a>
</div>
@endsection
