@extends('layout')
@section('content')
@php
$defaultRequestJson = json_encode(['bank_code' => '{bank_code}', 'account_number' => '{account_number}'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$defaultResponseJson = json_encode(['success' => true, 'data' => ['account_name' => 'NAMA PENERIMA', 'account_number' => '1234567890', 'bank_code' => 'BCA']], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp
<div class="rg-page-heading"><p>REKGO · ADMIN CENTER</p><h1>Verifikasi API Bank</h1><p>Konfigurasi provider pihak ketiga dengan request dan respons JSON yang berbeda-beda.</p></div>
<p><a class="btn secondary" href="/admin">← Dashboard Admin</a></p>
<section class="glass">
<div class="row" style="flex-wrap:wrap"><h2>Provider Verifikasi Rekening</h2><span class="pill">Belum aktif · Konfigurasi saja</span></div>
<p class="muted small">Simpan endpoint, kredensial, header, request dan pemetaan JSON. Sistem belum memanggil URL provider atau memverifikasi otomatis sampai adapter aman tersedia.</p>
<form method="post" action="/admin/bank-provider">@csrf
<h3>1. Identitas & Endpoint</h3>
<label for="bp-name">Nama penyedia API</label><input id="bp-name" name="provider_name" required maxlength="100" placeholder="Contoh: Provider Bank ABC" value="{{ old('provider_name',$settings['provider_name']??'') }}">
<label for="bp-endpoint">URL API HTTPS</label><input id="bp-endpoint" type="url" name="endpoint" required maxlength="500" placeholder="https://api.provider.com/bank/inquiry" value="{{ old('endpoint',$settings['endpoint']??'') }}">
<label for="bp-method">Metode request</label><select id="bp-method" name="method"><option value="POST" @selected(old('method',$settings['method']??'POST')==='POST')>POST</option><option value="GET" @selected(old('method',$settings['method']??'POST')==='GET')>GET</option></select>
<hr>
<h3>2. Autentikasi</h3>
<label for="bp-auth">Jenis autentikasi</label><select id="bp-auth" name="auth_type">
@foreach(['none'=>'Tanpa autentikasi','bearer'=>'Bearer Token','basic'=>'Basic Auth (Username + Password)','header'=>'API Key melalui Header'] as $value=>$label)
<option value="{{ $value }}" @selected(old('auth_type',$settings['auth_type']??'bearer')===$value)>{{ $label }}</option>
@endforeach
</select>
<label for="bp-username">Username / Client ID</label><input id="bp-username" name="username" maxlength="200" autocomplete="off" value="{{ old('username',$settings['username']??'') }}" placeholder="Username provider jika diperlukan">
<label for="bp-password">Password / Client Secret</label><input id="bp-password" type="password" name="password" maxlength="2048" autocomplete="new-password" placeholder="{{ $has_key ? 'Kosongkan untuk mempertahankan password lama' : 'Password jika diperlukan' }}">
<label for="bp-token">API Key / Token</label><input id="bp-token" type="password" name="api_key" maxlength="2048" autocomplete="new-password" placeholder="{{ $has_key ? 'Kosongkan untuk mempertahankan token lama' : 'Tempel token provider' }}">
<label for="bp-header">Nama header token</label><input id="bp-header" name="auth_header" maxlength="100" value="{{ old('auth_header',$settings['auth_header']??'Authorization') }}" placeholder="Authorization atau X-API-Key">
<label for="bp-prefix">Prefix nilai header</label><input id="bp-prefix" name="auth_prefix" maxlength="50" value="{{ old('auth_prefix',$settings['auth_prefix']??'Bearer ') }}" placeholder="Bearer (kosong jika tidak diperlukan)">
<hr>
<h3>3. Request API</h3>
<p class="muted small">Contoh placeholder yang dapat dipetakan nanti: <code>{{ '{bank_code}' }}</code> dan <code>{{ '{account_number}' }}</code>. Jangan menaruh password dalam contoh JSON.</p>
<label for="bp-headers">Header tambahan (JSON)</label><textarea id="bp-headers" name="headers_json" rows="3" style="font-family:monospace" placeholder='{"Accept":"application/json"}'>{{ old('headers_json',$settings['headers_json']??'{"Accept":"application/json"}') }}</textarea>
<label for="bp-query">Query parameter (JSON)</label><textarea id="bp-query" name="query_json" rows="3" style="font-family:monospace" placeholder='{"service":"bank_inquiry"}'>{{ old('query_json',$settings['query_json']??'{}') }}</textarea>
<label for="bp-request">Request Body (JSON)</label><textarea id="bp-request" name="request_example" rows="5" required style="font-family:monospace">{{ old('request_example', $settings['request_example'] ?? $defaultRequestJson) }}</textarea>
<hr>
<h3>4. Contoh Respons & Mapping JSON</h3>
<p class="muted small">Tempel respons contoh dari dokumentasi penyedia. Setiap field dapat berbeda; gunakan path bertitik, misalnya <code>data.account_name</code> atau <code>result.name</code>.</p>
<label for="bp-response">Contoh Response JSON</label><textarea id="bp-response" name="response_example" rows="7" required style="font-family:monospace">{{ old('response_example', $settings['response_example'] ?? $defaultResponseJson) }}</textarea>
<div class="grid rg-settings-grid">
<div><label>Path status</label><input name="status_path" required value="{{ old('status_path',$settings['status_path']??'success') }}"></div>
<div><label>Nilai status sukses</label><input name="success_value" required value="{{ old('success_value',$settings['success_value']??'true') }}"></div>
<div><label>Path nama rekening</label><input name="name_path" required value="{{ old('name_path',$settings['name_path']??'data.account_name') }}"></div>
<div><label>Path nomor rekening</label><input name="account_path" value="{{ old('account_path',$settings['account_path']??'data.account_number') }}"></div>
<div><label>Path kode bank</label><input name="bank_path" value="{{ old('bank_path',$settings['bank_path']??'data.bank_code') }}"></div>
<div><label>Path pesan error</label><input name="error_path" value="{{ old('error_path',$settings['error_path']??'message') }}"></div>
</div>
<button type="submit" style="width:100%;margin-top:12px">Simpan Konfigurasi Provider</button>
</form>
</section>
<section class="glass" style="margin-top:16px"><h3>Mode Aman</h3><p class="muted small">Kredensial disimpan terenkripsi, tidak ditampilkan kembali di halaman. Pengaturan ini belum mengaktifkan koneksi atau otomatis menyetujui rekening. Integrasi berikutnya wajib membatasi host API untuk mencegah SSRF, memeriksa respons bank dan nomor rekening, serta mencatat audit.</p></section>
@endsection