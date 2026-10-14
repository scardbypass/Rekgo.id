<?php
namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
class QrisController {
 public function show(Request $request, Payment $payment){
  abort_unless((int)$payment->user_id===(int)$request->user()->id,403);
  return view('qris-detail',['payment'=>$payment]);
 }
 public function image(Request $request, Payment $payment){
  abort_unless((int)$payment->user_id===(int)$request->user()->id,403);
  abort_unless($payment->status==='pending' && $payment->expires_at?->isFuture() && is_string($payment->qr_payload) && strlen($payment->qr_payload)>10,404);
  $svg=(new SvgWriter())->write(new QrCode(data:$payment->qr_payload,size:360,margin:16))->getString();
  return response($svg,200,['Content-Type'=>'image/svg+xml','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff','Content-Security-Policy'=>"default-src 'none'; style-src 'unsafe-inline'; sandbox"]);
 }
}
