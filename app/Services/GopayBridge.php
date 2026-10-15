<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use RuntimeException;
class GopayBridge {
 public function journal(array $filters=[]):array {
  // The private CeirGo-compatible service handles merchant auth and token renewal.
  // Never send merchant credentials from the public RekGo application.
  return $this->call('journal-search',$filters);
 }
 public function call(string $action,array $data=[]):array {
  $key=config('services.gopay_bridge.key');
  if(!$key || strlen($key)<32)throw new RuntimeException('GoPay bridge not configured');
  $url=config('services.gopay_bridge.url','http://127.0.0.1:4319');
  $response=Http::timeout(25)->withToken($key)->acceptJson()->post(rtrim($url,'/').'/v1/action',['action'=>$action]+$data);
  if(!$response->successful()||!$response->json('ok'))throw new RuntimeException('GoPay provider request failed');
  return $response->json('result',[]);
 }
}
