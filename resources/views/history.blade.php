@extends('layout')
@section('content')
<div class="rg-page-heading rg-page-title"><p>REKGO · AKTIVITAS</p><h1>Riwayat transaksi</h1><p>Pantau semua aktivitas pembayaran dan pencairan.</p></div>
<div class="rg-history-tabs" aria-label="Filter jenis transaksi">
 <a href="#riwayat-qris">▦ QRIS <span>{{ $payments->count() }}</span></a>
 <a href="#riwayat-transfer">↗ Transfer <span>{{ $withdrawals->count() }}</span></a>
 <a href="#riwayat-rekber">◇ Rekber <span>{{ $escrows->count() }}</span></a>
</div>
<div class="rg-history-stack">
<section class="glass rg-history-card" id="riwayat-qris">
 <div class="rg-history-head"><div class="rg-history-symbol rg-symbol-qris">▦</div><div class="rg-history-heading"><h2>QRIS</h2><p>Tagihan & pembayaran</p></div><a class="rg-action-link" href="/qris">Buka QRIS <span>→</span></a></div>
 <div class="rg-records">@forelse($payments as $p)<div class="rg-record"><div><strong>QRIS #{{ $p->id }}</strong><small>{{ $p->created_at?->format('d M Y, H:i') }}</small></div><div class="rg-record-right"><strong>Rp{{ number_format($p->gross,0,',','.') }}</strong><span class="pill">{{ $p->status }}</span></div></div>@empty<div class="rg-history-empty"><span>▦</span><strong>Belum ada pembayaran</strong><p>Riwayat QRIS akan tampil di sini.</p></div>@endforelse</div>
</section>
<section class="glass rg-history-card" id="riwayat-transfer">
 <div class="rg-history-head"><div class="rg-history-symbol rg-symbol-transfer">↗</div><div class="rg-history-heading"><h2>Transfer</h2><p>Pencairan saldo ke bank</p></div><a class="rg-action-link" href="/transfer">Buka transfer <span>→</span></a></div>
 <div class="rg-records">@forelse($withdrawals as $w)<div class="rg-record"><div><strong>Transfer #{{ $w->id }}</strong><small>{{ $w->created_at?->format('d M Y, H:i') }} · {{ $w->bank_code }}</small></div><div class="rg-record-right"><strong>Rp{{ number_format($w->amount,0,',','.') }}</strong><span class="pill">{{ $w->status }}</span></div></div>@empty<div class="rg-history-empty"><span>↗</span><strong>Belum ada pencairan</strong><p>Transfer yang kamu ajukan akan tercatat di sini.</p></div>@endforelse</div>
</section>
<section class="glass rg-history-card" id="riwayat-rekber">
 <div class="rg-history-head"><div class="rg-history-symbol rg-symbol-rekber">◇</div><div class="rg-history-heading"><h2>Rekber</h2><p>Transaksi melalui bot</p></div></div>
 <div class="rg-records">@forelse($escrows as $e)<div class="rg-record"><div><strong>{{ $e->title }}</strong><small>#{{ $e->id }} · {{ $e->created_at?->format('d M Y, H:i') }}</small></div><div class="rg-record-right"><strong>Rp{{ number_format($e->amount,0,',','.') }}</strong><span class="pill">{{ $e->status }}</span></div></div>@empty<div class="rg-history-empty"><span>◇</span><strong>Belum ada transaksi rekber</strong><p>Aktivitas rekber dari bot akan muncul di sini.</p></div>@endforelse</div>
</section>
</div>
@endsection
