@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin Center</p><h1>GoPay Merchant</h1><p>Konfigurasi QRIS dan pengecekan transaksi merchant.</p></div>
<p><a class="btn secondary" href="/admin/integrations">← Semua Integrasi</a></p>
<section class="glass"><h2>Pengaturan Payment Gateway</h2>
<p><span class="pill">{{ config('rekgo.gopay_live_verified') ? 'Verifikasi server aktif' : 'Mode aman · Live terkunci' }}</span></p>
<p class="muted small">Integrasi QRIS statis dengan nominal unik masih memerlukan adapter jurnal GoPay yang telah diuji. Pengaturan ini belum mengaktifkan pembayaran otomatis.</p>
<form method="post" action="/admin/integrations/pg">@csrf
<label><input type="checkbox" name="enabled" value="1" style="width:auto" {{ $pg&&$pg->enabled?'checked':'' }}> Aktifkan pembayaran live setelah verifikasi</label>
<label><input type="checkbox" name="on_demand" value="1" style="width:auto" {{ !$pg||$pg->on_demand?'checked':'' }}> Cek hanya ketika ada tagihan</label>
<label>Merchant ID</label><input name="merchant_id" value="{{ $pg->merchant_id??'' }}" maxlength="150">
<label>Interval pengecekan</label><select name="poll_seconds">@foreach([30,60,120] as $s)<option value="{{ $s }}" {{ ($pg->poll_seconds??60)==$s?'selected':'' }}>{{ $s }} detik</option>@endforeach</select>
<label>Batas waktu tagihan (menit)</label><input name="expiry_minutes" type="number" min="5" max="60" value="{{ $pg->expiry_minutes??10 }}">
<button type="submit">Simpan Pengaturan</button></form></section>
<div class="gap"></div><section class="glass"><h2>Koneksi Akun Merchant</h2>
<p class="muted small">Form OTP di bawah memerlukan bridge terotorisasi. Jangan masukkan password Gmail di RekGo.</p>
<form method="post" action="/admin/integrations/pg/otp/request">@csrf<label>Nomor Merchant (62...)</label><input name="phone" required pattern="62[0-9]{8,13}"><button type="submit">Minta OTP</button></form>
<form method="post" action="/admin/integrations/pg/otp/verify">@csrf<label>Kode OTP</label><input name="otp" required maxlength="8" autocomplete="one-time-code"><button type="submit">Verifikasi OTP</button></form>
</section>
@endsection