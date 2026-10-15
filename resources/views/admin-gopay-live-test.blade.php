@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Super Admin</p><h1>Verifikasi QRIS Otomatis</h1><p>Pembayaran pelanggan diperiksa melalui scheduler tanpa memasukkan referensi secara manual.</p></div>
<p><a class="btn secondary" href="/admin/integrations/gopay">← Pengaturan GoPay</a></p>
<section class="glass" style="max-width:650px;margin:0 auto">
<h2>Status Kesiapan</h2>
@if(!$ready)
<p class="rg-inline-notice">QRIS merchant belum tersedia. Simpan QRIS Text yang valid pada halaman GoPay.</p>
@else
<p>QRIS merchant tersimpan. Ini belum membuktikan bahwa koneksi jurnal GoBiz dan konfirmasi pembayaran otomatis sudah aktif.</p>
@endif
<p class="rg-inline-notice"><strong>Keamanan:</strong> jangan mengirim pembayaran uji atau deposit sebelum adapter jurnal GoBiz terhubung dan pengujian transaksi end-to-end dinyatakan berhasil.</p>
<h3>Alur otomatis</h3>
<p>Tagihan pending → scheduler mengecek provider → validasi referensi unik dan nominal → status berhasil → saldo diperbarui satu kali. Jika tidak dapat diverifikasi, pembayaran tetap pending untuk rekonsiliasi.</p>
<p class="muted small">Perintah scheduler yang sudah disiapkan: <code>php artisan schedule:run</code> (dipanggil setiap menit oleh cron). Mode live tetap terkunci sampai integrasi provider diverifikasi.</p>
</section>
@endsection
