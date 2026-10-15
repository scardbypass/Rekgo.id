@extends('layout')
@section('content')
<style>
.rq-wrap{max-width:490px;margin:0 auto 32px;padding:0 2px}
.rq-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:4px 0 18px}
.rq-back{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--text,#fff);font-size:13px;font-weight:650;opacity:.83}
.rq-id{font-size:11px;letter-spacing:.09em;font-weight:700;opacity:.55}
.rq-card{position:relative;overflow:hidden;padding:25px 22px 23px;border:1px solid rgba(169,199,255,.17);border-radius:30px;background:linear-gradient(150deg,rgba(37,68,112,.75),rgba(14,32,59,.92) 68%);box-shadow:0 22px 55px rgba(0,0,0,.16);color:#f8fbff;text-align:center}
.rq-card:before{content:"";position:absolute;inset:-90px -80px auto auto;width:230px;height:230px;background:radial-gradient(circle,rgba(84,149,255,.23),transparent 70%);pointer-events:none}
.rq-kicker{font-size:11px;font-weight:800;letter-spacing:.15em;color:#a9c5ed}
.rq-amount{font-size:clamp(34px,8vw,43px);letter-spacing:-.055em;font-weight:800;margin:8px 0 12px;line-height:1.2;font-variant-numeric:tabular-nums}
.rq-status{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:99px;font-size:12px;font-weight:700;background:rgba(255,193,89,.13);color:#ffda9c;border:1px solid rgba(255,193,89,.24)}
.rq-dot{width:7px;height:7px;background:currentColor;border-radius:50%}
.rq-qr-frame{background:#fff;padding:15px;border-radius:24px;margin:23px auto 12px;max-width:304px;box-shadow:0 15px 40px rgba(0,0,0,.17)}
.rq-qr-frame img{display:block;width:100%;height:auto;aspect-ratio:1}
.rq-scan{font-size:12px;color:#c0d3ef;margin:13px 0 0}
.rq-meta{display:flex;justify-content:space-between;gap:10px;align-items:center;text-align:left;padding:15px 0;border-bottom:1px solid rgba(207,225,255,.13)}
.rq-meta:first-child{border-top:1px solid rgba(207,225,255,.13)}
.rq-meta span{font-size:12px;color:#afc2df}.rq-meta strong{font-size:12px;font-weight:700;text-align:right}
.rq-help{margin-top:16px;padding:14px 16px;border-radius:18px;background:rgba(137,183,245,.08);border:1px solid rgba(171,207,255,.11);text-align:left}
.rq-help strong{font-size:13px}.rq-help p{font-size:12px;line-height:1.75;color:#b8cce9;margin:5px 0 0}
.rq-actions{display:flex;gap:10px;margin-top:17px}
.rq-action{flex:1;display:flex;justify-content:center;align-items:center;padding:13px 12px;border-radius:14px;font-weight:750;font-size:13px;text-decoration:none;background:#f3f7ff;color:#102849;border:1px solid rgba(255,255,255,.15)}
.rq-action.ghost{background:rgba(255,255,255,.06);color:#e3efff}
.rq-complete{padding:35px 0 26px;font-size:14px;line-height:1.7;color:#bcd0e9}
@media(max-width:420px){.rq-card{padding:23px 17px 19px;border-radius:25px}.rq-qr-frame{max-width:270px;padding:12px;border-radius:20px}.rq-wrap{padding:0 1px}.rq-actions{flex-direction:column}}
</style>
<div class="rq-wrap">
 <div class="rq-top">
  <a class="rq-back" href="/qris">← Kembali</a>
  <span class="rq-id">REKGO / QRIS #{{ $payment->id }}</span>
 </div>
 <section class="rq-card">
  <div class="rq-kicker">TOTAL PEMBAYARAN</div>
  <div class="rq-amount">Rp{{ number_format($payment->gross,0,',','.') }}</div>
  @if($payment->status==='pending')
   <span class="rq-status"><span class="rq-dot"></span>Menunggu pembayaran</span>
  @elseif($payment->status==='success')
   <span class="rq-status" style="color:#a3f0c4;background:rgba(66,196,125,.12);border-color:rgba(66,196,125,.25)">● Pembayaran berhasil</span>
  @else
   <span class="rq-status" style="color:#ffc0c0">● {{ ucfirst($payment->status) }}</span>
  @endif
  @if($payment->status==='pending' && $payment->expires_at?->isFuture() && $payment->qr_payload)
   <div class="rq-qr-frame">
    <img src="{{ route('qris.image',$payment) }}" alt="QRIS pembayaran RekGo #{{ $payment->id }}" width="276" height="276">
   </div>
   <p class="rq-scan">Scan menggunakan aplikasi bank atau e-wallet</p>
  @else
   <div class="rq-complete">Kode QR tidak tersedia untuk tagihan ini.<br>Jika sudah membayar, hubungi admin dengan nomor tagihan.</div>
  @endif
  <div style="margin-top:21px">
   <div class="rq-meta"><span>Nomor tagihan</span><strong>#{{ $payment->id }}</strong></div>
   <div class="rq-meta"><span>Metode</span><strong>QRIS • Verifikasi Admin</strong></div>
   @if($payment->expires_at)
    <div class="rq-meta"><span>Batas pembayaran</span><strong>{{ $payment->expires_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</strong></div>
   @endif
  </div>
  <div class="rq-help">
   <strong>Setelah melakukan pembayaran</strong>
   <p>Pastikan nama merchant dan nominal di aplikasi pembayaran sesuai. Admin akan mencocokkan mutasi dan menyetujui deposit. Jangan membayar ulang jika status belum berubah.</p>
  </div>
  <div class="rq-actions">
   <a href="/history" class="rq-action">Lihat riwayat</a>
   <a href="/dashboard" class="rq-action ghost">Dashboard</a>
  </div>
 </section>
</div>
@endsection
