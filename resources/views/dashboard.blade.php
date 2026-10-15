@extends('layout')
@section('content')
<div class="rg-page-heading rg-page-title"><p>REKGO · BERANDA</p><h1>Halo, {{ $u->username }} 👋</h1><p>Ringkasan saldo dan aktivitas akunmu.</p></div>
<section class="card rg-wallet" aria-label="Ringkasan saldo">
 <div class="row"><strong>RekGo Wallet</strong><span>◈</span></div>
 <p class="rg-wallet-caption">Total saldo</p><h1 class="rg-amount">Rp{{ number_format($b->available+$b->pending+$b->locked,0,',','.') }}</h1>
 <div class="rg-wallet-stats"><div><small>Tersedia</small><strong>Rp{{ number_format($b->available,0,',','.') }}</strong></div><div><small>Tertunda</small><strong>Rp{{ number_format($b->pending,0,',','.') }}</strong></div><div><small>Terkunci</small><strong>Rp{{ number_format($b->locked,0,',','.') }}</strong></div></div>
</section>
<div class="rg-section-heading"><h2>Akses cepat</h2></div>
<div class="rg-shortcuts">
 <a href="/transfer"><span class="rg-shortcut-icon rg-shortcut-transfer">↗</span><strong>Transfer</strong><small>Tarik saldo</small></a>
 <a href="/qris"><span class="rg-shortcut-icon rg-shortcut-qris">▦</span><strong>QRIS</strong><small>Terima pembayaran</small></a>
 <a href="/history"><span class="rg-shortcut-icon rg-shortcut-history">◷</span><strong>Riwayat</strong><small>Lihat transaksi</small></a>
 <a href="/account"><span class="rg-shortcut-icon rg-shortcut-account">♙</span><strong>Akun</strong><small>Pengaturan</small></a>
</div>
<section class="glass rg-dashboard-activity">
 <div class="rg-dashboard-activity-head"><div><h2>Aktivitas terbaru</h2><p class="muted small">Transaksi rekber dari bot</p></div><a class="rg-action-link" href="/history">Lihat semua <span>→</span></a></div>
 <div class="rg-records">@forelse($escrows as $e)<div class="rg-record"><div><strong>{{ $e->title }}</strong><small>#{{ $e->id }} · {{ $e->created_at?->format('d M Y, H:i') }}</small></div><div class="rg-record-right"><strong>Rp{{ number_format($e->amount,0,',','.') }}</strong><span class="pill">{{ $e->status }}</span></div></div>@empty<div class="rg-history-empty"><span>◷</span><strong>Belum ada aktivitas</strong><p>Transaksi rekber yang diproses bot akan tampil di sini.</p></div>@endforelse</div>
</section>
@endsection
