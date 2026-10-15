@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Super Admin</p><h1>Pengaturan API Verifikasi Bank</h1><p>Siapkan format request dan respons untuk integrasi provider pihak ketiga.</p></div>
<p><a class="btn secondary" href="/admin/page/users">← Semua Pengguna</a></p>
<section class="glass">
<h2>Template Provider</h2>
<p class="rg-inline-notice">Status: belum aktif. Halaman ini menyimpan konfigurasi saja, belum mengirim request atau menyetujui rekening otomatis.</p>
<form method="post" action="/admin/bank-provider">@csrf
<label>Endpoint HTTPS</label><input type="url" name="endpoint" required maxlength="500" placeholder="https://api.provider.example/v1/bank/inquiry" value="{{ old('endpoint',$settings['endpoint']??'') }}">
<label>Metode</label><select name="method"><option value="POST" @selected(old('method',$settings['method']??'POST')==='POST')>POST</option><option value="GET" @selected(old('method',$settings['method']??'POST')==='GET')>GET</option></select>
<label>Nama header autentikasi</label><input name="auth_header" required maxlength="100" value="{{ old('auth_header',$settings['auth_header']??'Authorization') }}">
<label>API Key / Token (tersimpan terenkripsi)</label><input name="api_key" type="password" maxlength="2048" autocomplete="new-password" placeholder="{{ $has_key ? 'Kosongkan untuk mempertahankan token' : 'Token provider' }}">
<label>Contoh request JSON</label><textarea name="request_example" rows="5" required style="font-family:monospace">{{ old('request_example',$settings['request_example']??'{"bank_code":"BCA","account_number":"1234567890"}') }}</textarea>
<label>Contoh response JSON</label><textarea name="response_example" rows="6" required style="font-family:monospace">{{ old('response_example',$settings['response_example']??'{"success":true,"data":{"account_name":"NAMA PEMILIK","account_number":"1234567890"}}') }}</textarea>
<label>Path nama pemilik rekening</label><input name="name_path" required value="{{ old('name_path',$settings['name_path']??'data.account_name') }}">
<label>Path status verifikasi</label><input name="status_path" required value="{{ old('status_path',$settings['status_path']??'success') }}">
<button type="submit">Simpan Template API</button>
</form>
<p class="muted small">Format di atas hanyalah contoh. Saat adapter dibuat, respons harus diverifikasi terhadap nomor rekening dan bank yang diminta, bukan sekadar nama dan status sukses.</p>
</section>
@endsection
