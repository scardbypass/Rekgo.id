@extends('layout')
@section('content')
<section class="glass" style="max-width:520px;margin:auto;text-align:center">
<h2>Detail Pembayaran QRIS #{{ $payment->id }}</h2>
<h1>Rp{{ number_format($payment->gross,0,',','.') }}</h1>
<p>Status: <strong>{{ strtoupper($payment->status) }}</strong></p>
@if($payment->status==='pending' && $payment->expires_at?->isFuture() && $payment->qr_payload)
<img src="{{ route('qris.image',$payment) }}" alt="QRIS nominal Rp{{number_format($payment->gross,0,',','.')}}" width="360" height="360" style="width:100%;max-width:360px;height:auto;background:white;border-radius:18px;padding:8px" />
<p>Berlaku sampai {{ $payment->expires_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</p>
<p class="muted">Bayar sesuai nominal. Status pembayaran diperiksa otomatis oleh server.</p>
@else
<p class="pending">QRIS tidak tersedia untuk dipindai. Jika pembayaran baru saja dilakukan, tunggu rekonsiliasi.</p>
@endif
<a href="/dashboard">Kembali ke Dashboard</a>
</section>
@endsection
