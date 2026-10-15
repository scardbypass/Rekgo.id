@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin Center</p><h1>GoPay Merchant</h1><p>Kelola akun GoBiz, QRIS statis, dan pengaturan pengecekan pembayaran.</p></div>
<p><a class="btn secondary" href="/admin/integrations">← Semua Integrasi</a></p>
@if($errors->any())
<section class="glass"><strong>Pengaturan belum tersimpan</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></section><div class="gap"></div>
@endif
@if(session('ok'))<section class="glass">{{ session('ok') }}</section><div class="gap"></div>@endif
<section class="glass"><h2>Mode Uji Live Khusus Super Admin</h2><p class="muted small">Lihat QRIS merchant asli untuk mencoba pembayaran manual. Tidak membuat tagihan, tidak mengkredit saldo, dan tidak mengaktifkan deposit pengguna.</p><a class="btn secondary" href="{{ route('admin.gopay.test') }}">Buka Mode Uji QRIS →</a></section><div class="gap"></div>\n<div class="grid rg-admin-grid">
<div class="glass"><span class="rg-icon">◇</span><h3>Akun GoBiz</h3><p class="muted">{{ $credentials_saved ? 'Kredensial tersimpan secara terenkripsi' : 'Belum dikonfigurasi' }}</p></div>
<div class="glass"><span class="rg-icon">◷</span><h3>Transaksi Otomatis</h3><p class="muted">{{ $pg && $pg->enabled ? 'Pengaturan aktif' : 'Belum aktif' }} · Belum terverifikasi live</p></div>
</div>
<div class="gap"></div>
<section class="glass"><h2>1. Koneksi Akun GoBiz</h2>
<p class="muted small">Masukkan email dan password merchant. Informasi disimpan terenkripsi pada storage privat server, bukan ditampilkan di browser. Gunakan email dan password akun merchant GoBiz milik sendiri. Penyimpanan ini belum melakukan login ke provider.</p>
<form method="post" action="/admin/integrations/gopay/credentials">@csrf
<label for="gobiz-email">GoBiz Email</label><input id="gobiz-email" type="email" name="email" required maxlength="254" autocomplete="username" placeholder="merchant@example.com" value="{{ old('email') }}">
<label for="gobiz-password">GoBiz Password</label><input id="gobiz-password" type="password" name="password" {{ $credentials_saved ? '' : 'required' }} maxlength="512" autocomplete="new-password" placeholder="{{ $credentials_saved ? 'Kosongkan jika tidak ingin mengganti' : 'Password merchant' }}">
<label><input type="checkbox" style="width:auto" onclick="document.getElementById('gobiz-password').type=this.checked?'text':'password'"> Tampilkan password saat mengetik</label>
<button type="submit">Simpan Akun GoBiz</button></form>
<p class="muted small">Langkah: (1) Masukkan email GoBiz, (2) masukkan password, (3) klik Simpan Akun GoBiz, (4) tempel QRIS Text merchant pada bagian 3, (5) jalankan pengujian khusus Super Admin. Token dan pengecekan jurnal otomatis belum terhubung; jangan mengaktifkan live sebelum berhasil diuji.</p></section>
<div class="gap"></div>
<section class="glass"><h2>2. Pengaturan QRIS Statis</h2>
<p><span class="pill">{{ config('rekgo.gopay_live_verified') ? 'Server ditandai terverifikasi' : 'Live terkunci untuk keamanan' }}</span></p>
<p class="muted small">Mode QRIS statis + nominal unik sedang dipersiapkan. Jangan mengaktifkan transaksi live sebelum QRIS merchant dan rekonsiliasi jurnal terbukti sesuai.</p>
<form method="post" action="/admin/integrations/pg">@csrf
<label>Merchant ID</label><input name="merchant_id" value="{{ $pg->merchant_id??'' }}" maxlength="150" placeholder="ID merchant resmi">
<label>Interval cek jurnal</label><select name="poll_seconds">@foreach([30,60,120] as $s)<option value="{{ $s }}" {{ ($pg->poll_seconds??60)==$s?'selected':'' }}>{{ $s }} detik</option>@endforeach</select>
<label>Batas waktu pembayaran (menit)</label><input name="expiry_minutes" type="number" min="5" max="60" value="{{ $pg->expiry_minutes??10 }}">
<label><input type="checkbox" name="on_demand" value="1" style="width:auto" {{ !$pg||$pg->on_demand?'checked':'' }}> Periksa hanya ketika ada tagihan</label>
<label><input type="checkbox" name="enabled" value="1" style="width:auto" {{ $pg&&$pg->enabled?'checked':'' }}> Aktifkan pembayaran live setelah uji berhasil</label>
<button type="submit">Simpan Pengaturan QRIS</button></form></section>
<div class="gap"></div>
<section class="glass"><h2>3. GoPay QRIS Text</h2>
<p class="muted small">Tempel payload QRIS statis resmi merchant. Teks ditampilkan lengkap dan dapat diedit.</p>
<form method="post" action="/admin/integrations/gopay/qris-text">@csrf
<label for="qris-text">QRIS Text</label>
@if($qris_text !== '')
<p class="muted small">QRIS tersimpan. Payload lengkap tetap ditampilkan di bawah dan bisa diperbarui.</p>
@else
<p class="muted small">Belum ada QRIS Text yang tersimpan.</p>
@endif
<textarea id="qris-text" name="qris_text" rows="6" required minlength="20" maxlength="4096" style="width:100%;font-family:monospace">{{ old('qris_text',$qris_text) }}</textarea>
<button type="submit">Simpan QRIS Text</button></form>
@if($qris_text !== '')
<details style="margin-top:16px"><summary><strong>Lihat QRIS Text tersimpan</strong></summary>
<pre style="white-space:pre-wrap;overflow-wrap:anywhere;user-select:text;padding:12px;border:1px solid var(--line);border-radius:12px">{{ $qris_text }}</pre>
</details>
@endif
<p class="muted small">Tersimpan terenkripsi. Pembayaran otomatis tetap nonaktif sampai pengujian selesai.</p></section>
<div class="gap"></div>
<section class="glass"><h2>4. Status & Monitoring</h2>
<p class="muted small">Koneksi provider: belum diuji · Token TTL: belum tersedia · Sinkronisasi jurnal: belum tersedia.</p>
<p class="muted small">Tidak ada status online atau transaksi berhasil yang ditampilkan sebelum ada respons nyata dari adapter GoBiz.</p>
</section>
@endsection
