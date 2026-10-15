@extends('layout')
@section('content')
<div class="rg-dash-top"><div class="rg-page-heading rg-page-title"><p>REKGO · ADMIN CENTER</p><h1>Dashboard Admin</h1><p>Kelola operasional dan pantau aktivitas RekGo.</p></div><a class="rg-dash-admin" href="/dashboard"><span>←</span> Dashboard User</a></div>
<section class="card rg-wallet"><div class="row"><strong>Saldo Seluruh Akun</strong><span>◆ RekGo</span></div><p class="rg-wallet-caption">Total saldo tercatat</p><h1 class="rg-amount">Rp{{number_format(($balances->available??0)+($balances->pending??0)+($balances->locked??0),0,',','.')}}</h1><div class="rg-wallet-stats"><div><small>Tersedia</small><strong>Rp{{number_format($balances->available??0,0,',','.')}}</strong></div><div><small>Tertunda</small><strong>Rp{{number_format($balances->pending??0,0,',','.')}}</strong></div><div><small>Terkunci</small><strong>Rp{{number_format($balances->locked??0,0,',','.')}}</strong></div></div></section>
<h2 class="rg-section-label">Kelola RekGo</h2>
<div class="grid rg-admin-grid">
<a class="glass" href="/admin/page/acc"><span class="rg-icon">↗</span><h2>Pencairan</h2><p class="muted">Persetujuan dan status WD</p></a>
<a class="glass" href="/admin/page/users"><span class="rg-icon">♙</span><h2>Pengguna</h2><p class="muted">Daftar user dan seller</p></a>
<a class="glass" href="/admin/banks"><span class="rg-icon">▣</span><h2>Manajemen Bank</h2><p class="muted">Tambah dan nonaktifkan bank</p></a><a class="glass" href="/admin/page/fee"><span class="rg-icon">◇</span><h2>Biaya layanan</h2><p class="muted">Pengaturan tier fee</p></a>
<a class="glass" href="/admin/page/admins"><span class="rg-icon">♧</span><h2>Admin</h2><p class="muted">Kelola akses staf</p></a>
<a class="glass" href="/admin/page/audits"><span class="rg-icon">◷</span><h2>Audit log</h2><p class="muted">Riwayat tindakan admin</p></a>
<a class="glass" href="/admin/integrations"><span class="rg-icon">⌘</span><h2>Integrasi</h2><p class="muted">Bot dan payment gateway</p></a>
@if(auth()->user()->role === 'super_admin')
<a class="glass" href="/admin/bank-provider"><span class="rg-icon">▣</span><h2>Verifikasi API Bank</h2><p class="muted">Endpoint, kredensial, request & mapping JSON</p></a>
@endif
<a class="glass" href="/admin/v4"><span class="rg-icon">⚙</span><h2>Pengaturan</h2><p class="muted">Maintenance dan pengumuman</p></a>
</div>
<section class="glass rg-compact-panel rg-panel-spaced"><div class="rg-history-head"><span class="rg-history-symbol rg-symbol-transfer">↗</span><div class="rg-history-heading"><h2>Pencairan terbaru</h2><p>Permintaan WD yang perlu dipantau</p></div><a class="rg-action-link" href="/admin/page/acc">Semua <span>→</span></a></div><div class="rg-records">@forelse($withdrawals->take(5) as $wd)<div class="rg-record"><div><strong>{{ $wd->user?->username ?? 'Pengguna' }}</strong><small>WD #{{ $wd->id }} · {{ $wd->status }}</small></div><div class="rg-record-right"><strong>Rp{{ number_format($wd->amount,0,',','.') }}</strong></div></div>@empty<div class="rg-history-empty"><span>↗</span><strong>Belum ada pencairan</strong><p>Permintaan transfer akan tampil di sini.</p></div>@endforelse</div></section>
@endsection
