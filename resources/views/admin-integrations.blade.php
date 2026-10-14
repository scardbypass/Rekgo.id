@extends('layout')
@section('content')
<h2>RekGo V4 • WhatsApp & Payment Center</h2>
<p><a class="btn secondary" href="/admin">← Dashboard Admin</a></p>
<section class="glass"><h3>Tambah Nomor WhatsApp Bot</h3><p class="muted small">Registrasi nomor tersedia. QR, pairing dan status online baru aktif setelah Neoxr/Baileys bridge dipasang. Tidak ada QR atau pairing palsu.</p>
<form method="post" action="/admin/integrations/bots">@csrf
<label>Nama Bot</label><input name="label" required maxlength="100" placeholder="RekGo Utama">
<label>Nomor WhatsApp (62...)</label><input name="phone" required pattern="62[0-9]{8,13}" placeholder="6281234567890">
<label>Metode Koneksi</label><select name="connection_method"><option value="qr">QR Code</option><option value="pair">Pairing Code</option></select>
<button>Daftarkan Nomor</button></form></section>
<div class="gap"></div><section class="glass"><h3>Daftar Bot</h3>
@forelse($bots as $bot)<div style="padding:12px 0;border-bottom:1px solid var(--line)"><b>{{ $bot->label }}</b> • {{ $bot->phone }}<p class="muted small">Status: {{ $bot->status }} • Metode: {{ $bot->connection_method }} • {{ $bot->enabled?'Aktif':'Nonaktif' }}</p>
<form method="post" action="/admin/integrations/bots/{{ $bot->id }}/toggle">@csrf<input type="hidden" name="enabled" value="{{ $bot->enabled?0:1 }}"><button class="secondary">{{ $bot->enabled?'Nonaktifkan':'Aktifkan' }}</button></form></div>
@empty<p>Belum ada bot terdaftar.</p>@endforelse</section>
<div class="gap"></div><section class="glass"><h3>QRIS GoPay MARC • On-Demand</h3><p class="muted small">Pengaturan GoPay. Aktivasi live terkunci sampai GOPAY_LIVE_VERIFIED=true setelah uji merchant dan rekonsiliasi.</p>
<form method="post" action="/admin/integrations/pg">@csrf
<label><input type="checkbox" name="enabled" value="1" style="width:auto" {{ $pg&&$pg->enabled?'checked':'' }}> Aktifkan QRIS live (perlu verifikasi server)</label><label><input type="checkbox" name="on_demand" value="1" style="width:auto" {{ !$pg||$pg->on_demand?'checked':'' }}> On-Demand (tanpa polling saat tidak ada tagihan)</label>
<label>Merchant ID (bukan password/token)</label><input name="merchant_id" value="{{ $pg->merchant_id??'' }}" maxlength="150">
<label>Interval pengecekan</label><select name="poll_seconds">@foreach([30,60,120] as $s)<option value="{{$s}}" {{($pg->poll_seconds??60)==$s?'selected':''}}>{{$s}} detik</option>@endforeach</select>
<label>QRIS kedaluwarsa (menit)</label><input name="expiry_minutes" type="number" min="5" max="60" value="{{ $pg->expiry_minutes??10 }}">
<button>Simpan Pengaturan</button></form><p class="muted small">Aktivasi harus melalui pengujian nyata; jangan gunakan transaksi produksi sebelum rekonsiliasi tervalidasi.</p></section>
<div class="gap"></div><section class="glass"><h3>Login GoPay Merchant • OTP</h3><p class="muted small">Khusus Super Admin. Token OTP disimpan sementara di sesi terenkripsi; access/refresh token hanya disimpan di service bridge VPS.</p><form method="post" action="/admin/integrations/pg/otp/request">@csrf<label>Nomor Merchant (62...)</label><input name="phone" required pattern="62[0-9]{8,13}"><button>Minta OTP</button></form><form method="post" action="/admin/integrations/pg/otp/verify">@csrf<label>Kode OTP</label><input name="otp" required maxlength="8" autocomplete="one-time-code"><button>Verifikasi OTP</button></form></section>@endsection
