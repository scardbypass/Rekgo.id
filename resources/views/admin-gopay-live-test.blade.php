@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Super Admin</p><h1>Mode Uji QRIS Live</h1><p>QRIS merchant asli, tanpa simulasi dan tanpa kredit saldo otomatis.</p></div>
<p><a class="btn secondary" href="/admin/integrations/gopay">← Pengaturan GoPay</a></p>
<section class="glass" style="max-width:650px;margin:0 auto">
<h2>Uji Pembayaran Manual</h2>
@if(!$ready)
<p class="rg-inline-notice">QRIS merchant belum tersedia atau tidak dapat dibaca. Simpan QRIS Text valid pada halaman GoPay terlebih dahulu.</p>
@else
<p class="muted">QR di bawah berasal dari QRIS Text merchant yang disimpan di server. Ini adalah QRIS <strong>statis</strong>, bukan tagihan dinamis.</p>
<div style="text-align:center"><img src="{{ route('admin.gopay.test.image') }}" alt="QRIS merchant asli untuk uji manual" width="360" height="360" style="max-width:100%;height:auto;background:#fff;padding:12px;border-radius:16px"></div>
<p><strong>Nominal contoh: Rp1.001</strong> — masukkan sendiri pada aplikasi pembayaran jika QRIS meminta nominal. Periksa nominal dan nama merchant sebelum konfirmasi.</p>
<p class="rg-inline-notice"><strong>Perhatian:</strong> pembayaran ini menggunakan uang sungguhan. Halaman ini belum membuat tagihan unik, belum memeriksa jurnal GoPay, dan <strong>tidak menambah saldo RekGo</strong>. Jangan gunakan untuk deposit pelanggan. Periksa mutasi langsung di akun merchant setelah membayar.</p>
@endif
</section>
@endsection
