@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Super Admin</p><h1>Mode Uji QRIS Live</h1><p>QRIS merchant dengan nominal Rp1.001 yang disisipkan untuk uji kompatibilitas.</p></div>
<p><a class="btn secondary" href="/admin/integrations/gopay">← Pengaturan GoPay</a></p>
<section class="glass" style="max-width:650px;margin:0 auto">
<h2>Uji Pembayaran Manual</h2>
@if(!$ready)
<p class="rg-inline-notice">QRIS merchant belum tersedia atau tidak dapat dibaca. Simpan QRIS Text valid pada halaman GoPay terlebih dahulu.</p>
@else
<p class="muted">QR di bawah berasal dari QRIS Text merchant yang disimpan di server. QRIS asli merchant dimodifikasi untuk menyertakan tag nominal Rp1.001 dan checksum baru. Belum tentu diterima oleh acquirer.</p>
<div style="text-align:center"><img src="{{ route('admin.gopay.test.image') }}" alt="QRIS merchant asli untuk uji manual" width="360" height="360" style="max-width:100%;height:auto;background:#fff;padding:12px;border-radius:16px"></div>
<p><strong>Nominal uji: Rp1.001</strong> — seharusnya muncul otomatis jika aplikasi bank dan acquirer mendukung. Jika nominal tidak muncul atau berbeda, batalkan pembayaran. Periksa nama merchant sebelum konfirmasi.</p>
<p class="rg-inline-notice"><strong>Perhatian:</strong> pembayaran ini menggunakan uang sungguhan. QRIS ini hanya untuk uji kompatibilitas payload, belum membuat tagihan unik, belum memeriksa jurnal GoPay, dan <strong>tidak menambah saldo RekGo</strong>. Jangan gunakan untuk deposit pelanggan. Periksa mutasi langsung di akun merchant setelah membayar.</p>
@endif
</section>
@endsection
