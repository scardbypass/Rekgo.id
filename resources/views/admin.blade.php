@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin Center</p><h1>Selamat datang, {{ auth()->user()->name }}</h1><p>Pantau operasional RekGo melalui satu pusat kontrol.</p></div>
<section class="card"><div class="row"><strong>Saldo Seluruh Akun</strong><span>◆ RekGo</span></div><p class="muted" style="margin-top:22px">Total saldo tercatat</p><h1 class="rg-amount">Rp{{number_format(($balances->available??0)+($balances->pending??0)+($balances->locked??0),0,',','.')}}</h1><div class="row rg-stat-row"><div><small>Tersedia</small><br><strong>Rp{{number_format($balances->available??0,0,',','.')}}</strong></div><div><small>Tertunda</small><br><strong>Rp{{number_format($balances->pending??0,0,',','.')}}</strong></div><div><small>Terkunci</small><br><strong>Rp{{number_format($balances->locked??0,0,',','.')}}</strong></div></div></section>
<h2 class="rg-section-label">Kelola RekGo</h2>
<div class="grid rg-admin-grid">
<a class="glass" href="/admin/page/acc"><span class="rg-icon">↗</span><h2>Pencairan</h2><p class="muted">Persetujuan dan status WD</p></a>
<a class="glass" href="/admin/page/users"><span class="rg-icon">♙</span><h2>Pengguna</h2><p class="muted">Daftar user dan seller</p></a>
<a class="glass" href="/admin/page/fee"><span class="rg-icon">◇</span><h2>Biaya layanan</h2><p class="muted">Pengaturan tier fee</p></a>
<a class="glass" href="/admin/page/admins"><span class="rg-icon">♧</span><h2>Admin</h2><p class="muted">Kelola akses staf</p></a>
<a class="glass" href="/admin/page/audits"><span class="rg-icon">◷</span><h2>Audit log</h2><p class="muted">Riwayat tindakan admin</p></a>
<a class="glass" href="/admin/integrations"><span class="rg-icon">⌘</span><h2>Integrasi</h2><p class="muted">Bot dan payment gateway</p></a>
<a class="glass" href="/admin/v4"><span class="rg-icon">⚙</span><h2>Pengaturan</h2><p class="muted">Maintenance dan pengumuman</p></a>
</div>
<section class="glass" style="margin-top:20px"><div class="row"><h2>Pencairan terbaru</h2><a href="/admin/page/acc">Lihat semua →</a></div><div class="scroll"><table><thead><tr><th>ID</th><th>Pengguna</th><th>Nominal</th><th>Status</th></tr></thead><tbody>@forelse($withdrawals->take(5) as $wd)<tr><td>#{{$wd->id}}</td><td>{{$wd->user->username??'-'}}</td><td>Rp{{number_format($wd->amount,0,',','.')}}</td><td>{{$wd->status}}</td></tr>@empty<tr><td colspan="4">Belum ada pencairan.</td></tr>@endforelse</tbody></table></div></section>
@endsection