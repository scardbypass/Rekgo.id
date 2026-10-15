@extends('layout')
@section('content')
<div class="rg-page-heading rg-admin-subhead"><a href="/admin" class="rg-dash-admin rg-back-button"><span aria-hidden="true">←</span> Admin Center</a><h1>{{ ['acc'=>'Pencairan & Persetujuan','users'=>'Pengguna RekGo','fee'=>'Pengaturan Biaya','admins'=>'Manajemen Admin','audits'=>'Audit Log'][$page] ?? 'Admin' }}</h1><p>RekGo • by CeirGo</p></div>
@if($page==='acc')
@php
$pendingCount=$withdrawals->where('status','pending')->count();
$processingCount=$withdrawals->where('status','processing')->count();
$completedCount=$withdrawals->where('status','success')->count();
$canFinance=in_array(auth()->user()->role,['super_admin','finance'],true);
@endphp
<div class="rg-admin-metrics">
 <div class="glass rg-metric"><span>Menunggu review</span><strong>{{ $pendingCount }}</strong><small>Pengajuan masuk</small></div>
 <div class="glass rg-metric"><span>Sedang diproses</span><strong>{{ $processingCount }}</strong><small>Menunggu konfirmasi bank</small></div>
 <div class="glass rg-metric"><span>Berhasil</span><strong>{{ $completedCount }}</strong><small>Pencairan selesai</small></div>
</div>
<section class="glass rg-admin-panel" id="acc">
 <div class="row rg-panel-heading"><div><h2>Daftar pencairan</h2><p class="muted small">Periksa detail dan status setiap permintaan transfer.</p></div><span class="pill">{{ $withdrawals->count() }} transaksi terbaru</span></div>
 <div class="rg-admin-records">
 @forelse($withdrawals as $wd)
 @php
  $state=['pending'=>['Menunggu','rg-status-wait'],'processing'=>['Diproses','rg-status-progress'],'success'=>['Berhasil','rg-status-done'],'rejected'=>['Ditolak','rg-status-failed']][$wd->status]??[ucfirst($wd->status),'rg-status-wait'];
 @endphp
 <details class="rg-withdrawal">
  <summary class="rg-withdrawal-summary">
   <span class="rg-wd-icon">↗</span>
   <span class="rg-wd-primary"><strong>{{ $wd->user?->username ?? 'Pengguna tidak tersedia' }}</strong><small>WD #{{ $wd->id }} · {{ $wd->created_at?->format('d M Y, H:i') }}</small></span>
   <span class="rg-wd-amount"><strong>Rp{{ number_format($wd->amount,0,',','.') }}</strong><span class="rg-status {{ $state[1] }}">{{ $state[0] }}</span></span>
   <span class="rg-chevron" aria-hidden="true">⌄</span>
  </summary>
  <div class="rg-withdrawal-body">
   <div class="rg-wd-details">
    <div><small>Bank tujuan</small><strong>{{ $wd->bank_code ?: 'Belum tersedia' }}</strong></div>
    <div><small>Nomor rekening</small><strong>{{ $canFinance ? ($wd->bank_account ?: '-') : '••••'.substr((string)$wd->bank_account,-4) }}</strong></div>
    <div><small>Nama pemilik</small><strong>{{ $canFinance ? ($wd->bank_name ?: 'Belum diverifikasi') : 'Hanya Finance' }}</strong></div>
    <div><small>Verifikasi bank</small><strong>{{ $wd->verified_at ? 'Terverifikasi' : 'Belum terverifikasi' }}</strong></div>
   </div>
   @if($wd->rejected_reason)<p class="rg-wd-alert">Alasan penolakan: {{ $wd->rejected_reason }}</p>@endif
   @if($canFinance)
    @if($wd->status==='pending')
      <div class="rg-wd-actions">
       <form method="post" action="/admin/withdrawals/{{ $wd->id }}/process">@csrf<button @disabled(!$wd->verified_at || !$wd->bank_name)>Mulai proses transfer →</button></form>
       @unless($wd->verified_at && $wd->bank_name)<p class="muted small">Proses dikunci sampai verifikasi rekening selesai.</p>@endunless
      </div>
      <details class="rg-reject-panel"><summary>Tolak pengajuan</summary><form method="post" action="/admin/withdrawals/{{ $wd->id }}/reject">@csrf<label for="reason-{{ $wd->id }}">Alasan penolakan</label><textarea id="reason-{{ $wd->id }}" name="reason" required minlength="5" maxlength="1000" rows="2"></textarea><button type="submit" class="rg-danger-button">Konfirmasi penolakan</button></form></details>
    @elseif($wd->status==='processing')
      <div class="rg-wd-actions"><h3>Konfirmasi transfer bank</h3><p class="muted small">Hanya konfirmasi setelah dana benar-benar terkirim dan referensi bank cocok.</p>
       <form method="post" enctype="multipart/form-data" action="/admin/withdrawals/{{ $wd->id }}/success">@csrf
        <label for="ref-{{ $wd->id }}">Referensi bank</label><input id="ref-{{ $wd->id }}" name="bank_reference" required maxlength="150" autocomplete="off">
        <label for="proof-{{ $wd->id }}">Bukti transfer (opsional)</label><input id="proof-{{ $wd->id }}" type="file" name="proof" accept="image/jpeg,image/png,application/pdf">
        <label for="notes-{{ $wd->id }}">Catatan</label><textarea id="notes-{{ $wd->id }}" name="notes" maxlength="1000" rows="2"></textarea>
        <button type="submit">Konfirmasi transfer berhasil</button>
       </form>
      </div>
    @elseif($wd->proof_path)
      <a class="btn secondary" href="/admin/withdrawals/{{ $wd->id }}/proof">Unduh bukti transfer</a>
    @endif
   @else
    <p class="muted small">Aksi keuangan hanya tersedia untuk Finance dan Super Admin.</p>
   @endif
  </div>
 </details>
 @empty
 <div class="rg-empty"><span>↗</span><h3>Belum ada pencairan</h3><p class="muted">Pengajuan transfer baru akan tampil di sini.</p></div>
 @endforelse
 </div>
</section>
@endif
@if($page==='users')
<section class="glass rg-admin-panel" id="users">
 <div class="rg-admin-section-title"><div><h2>Pengguna RekGo</h2><p class="muted small">Pilih akun untuk melihat saldo, QRIS, dan riwayat pencairan.</p></div><span class="pill">{{ $users->count() }} akun</span></div>
 <div class="rg-user-list">@forelse($users as $u)
 <a class="rg-user-card" href="/admin/users/{{ $u->id }}"><span class="rg-user-initial">{{ strtoupper(substr($u->username,0,1)) }}</span><span class="rg-user-info"><strong>{{ $u->username }}</strong><small>{{ $u->email }}</small></span><span class="rg-user-role">{{ ucfirst(str_replace('_',' ',$u->role)) }}</span><span class="rg-user-arrow">→</span></a>
 @empty<div class="rg-empty"><h3>Belum ada pengguna</h3><p class="muted">Akun yang terdaftar akan tampil di sini.</p></div>@endforelse</div>
</section>
@endif
@if($page==='fee')
<section class="glass rg-admin-panel" style="margin-bottom:15px"><h2>Biaya Transfer Bank (WD)</h2><p class="muted small">Biaya tetap yang ditambahkan saat seller mengajukan pencairan. Biaya tersimpan per transaksi dan dikembalikan jika WD ditolak.</p><p>Tarif saat ini: <strong>Rp{{ number_format((int)(\Illuminate\Support\Facades\DB::table('system_settings')->where('key','bank_transfer_fee')->value('value')??0),0,',','.') }}</strong></p>@if(auth()->user()->role==='super_admin')<form method="post" action="/admin/bank-transfer-fee">@csrf<label for="bank-fee">Biaya transfer per WD (Rp)</label><input id="bank-fee" type="number" name="fee" min="0" max="1000000" step="1" value="{{ (int)(\Illuminate\Support\Facades\DB::table('system_settings')->where('key','bank_transfer_fee')->value('value')??0) }}" required><button type="submit">Simpan Biaya Transfer</button></form>@endif</section><section class="glass rg-admin-panel" id="fee">
 <div class="rg-admin-section-title"><div><h2>Pengaturan Fee</h2><p class="muted small">Atur tarif berdasarkan jenis layanan dan batas nominal transaksi.</p></div></div>
 <div class="rg-fee-list">@forelse($tiers as $t)
 <div class="rg-fee-item"><div><strong>{{ strtoupper($t->scope) }}</strong><small>Mulai Rp{{ number_format($t->minimum,0,',','.') }}</small></div><span class="rg-fee-value">@if($t->type==='percent'){{ number_format($t->value/100,2,',','.') }}% @else Rp{{ number_format($t->value,0,',','.') }} @endif</span></div>
 @empty<div class="rg-fee-empty"><strong>Belum ada tier biaya</strong><p class="muted small">Tambahkan aturan biaya pertama melalui formulir di bawah.</p></div>@endforelse</div>
 @if(auth()->user()->role==='super_admin')
 <div class="rg-fee-form-head"><h3>Tambah tier biaya</h3><p class="muted small">Persentase memakai basis poin: 100 = 1%, 250 = 2,5%. Perubahan hanya berlaku untuk transaksi berikutnya.</p></div>
 <form method="post" action="/admin/fees" class="rg-fee-form">@csrf
 <div class="rg-form-grid"><div><label for="fee-scope">Jenis layanan</label><select id="fee-scope" name="scope" required><option value="escrow">Rekber</option><option value="qris">QRIS</option></select></div><div><label for="fee-min">Minimal nominal (Rp)</label><input id="fee-min" type="number" name="minimum" min="0" step="1" placeholder="Contoh: 10000" required></div><div><label for="fee-type">Jenis fee</label><select id="fee-type" name="type" required><option value="flat">Nominal tetap (Rp)</option><option value="percent">Persentase (basis poin)</option></select></div><div><label for="fee-value">Nilai fee</label><input id="fee-value" type="number" name="value" min="0" step="1" placeholder="Contoh: 100" required></div></div>
 <button type="submit" class="rg-fee-submit">Simpan tier biaya →</button></form>
 @endif
</section>
@endif
@if($page==='admins')
<section class="glass" id="admins"><h2>Tambah Admin</h2>@if(auth()->user()->role==='super_admin')<form method="post" action="/admin/admins">@csrf<label>Nama</label><input name="name" required><label>Username</label><input name="username" required><label>Email</label><input name="email" type="email" required><label>Role</label><select name="role"><option value="finance">Finance</option><option value="support">Support</option><option value="super_admin">Super Admin</option></select><button>Tambah Akun Nonaktif</button></form><p class="small muted">Akun belum dapat login hingga aktivasi undangan yang aman diintegrasikan.</p>@else<p>Hanya Super Admin.</p>@endif</section>
@endif
@if($page==='audits')
<section class="glass" id="audits"><h2>Audit Log</h2><div class="scroll"><table><tr><th>Waktu</th><th>Admin</th><th>Aksi</th><th>Target</th></tr>@foreach($audits as $a)<tr><td>{{$a->created_at}}</td><td>{{$a->actor_id}}</td><td>{{$a->action}}</td><td>{{$a->subject_type}} #{{$a->subject_id}}</td></tr>@endforeach</table></div></section>
@endif
@endsection
