@extends('layout')
@section('content')
<style>
/* RekGo auth — compact mobile-first liquid glass */
.rg-auth{max-width:460px;margin:8px auto 55px;position:relative}
.rg-auth-head{text-align:left;margin:0 0 18px}
.rg-auth-back{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--blue);font-size:12px;font-weight:800;padding:9px 12px;border-radius:999px;background:rgba(69,139,245,.10);border:1px solid rgba(69,139,245,.15);margin-bottom:21px}
.rg-auth-kicker{display:block;color:var(--blue);font-size:10px;letter-spacing:.17em;font-weight:850;text-transform:uppercase;margin-bottom:7px}
.rg-auth-head h1{font-size:clamp(27px,5vw,35px);line-height:1.14;letter-spacing:-.055em;margin:0 0 9px;font-weight:850}
.rg-auth-head p{color:var(--muted);font-size:13px;line-height:1.55;margin:0;max-width:350px}
.rg-auth-card{position:relative;overflow:hidden;padding:clamp(19px,4vw,28px)!important;border-radius:26px!important;border:1px solid rgba(130,170,226,.25)!important;background:linear-gradient(145deg,rgba(255,255,255,.84),rgba(239,247,255,.69))!important;box-shadow:0 20px 60px rgba(20,67,133,.10),inset 0 1px rgba(255,255,255,.88)!important;backdrop-filter:blur(28px)}
.rg-auth-card:before{content:"";position:absolute;top:-90px;right:-85px;width:220px;height:220px;background:radial-gradient(circle,rgba(66,149,255,.16),transparent 70%);pointer-events:none}
.rg-auth-switch{position:relative;display:grid;grid-template-columns:1fr 1fr;gap:4px;padding:5px;border-radius:16px;background:rgba(76,126,194,.09);border:1px solid rgba(112,157,216,.12);margin-bottom:22px}
.rg-auth-switch button{background:transparent!important;color:var(--muted)!important;padding:12px 8px;border-radius:12px;box-shadow:none!important;font-size:13px;font-weight:800}
.rg-auth-switch button[aria-selected=true]{background:linear-gradient(135deg,#4b9cff,#1769f4)!important;color:#fff!important;box-shadow:0 5px 14px rgba(23,105,244,.22)!important}
.rg-auth-form[hidden]{display:none!important}
.rg-auth-form h2{font-size:20px;letter-spacing:-.04em;margin:0 0 4px}
.rg-auth-hint{font-size:12px;color:var(--muted);margin:0 0 16px}
.rg-auth-form label{display:block;margin:13px 0 5px;font-size:12px;font-weight:750}
.rg-auth-form input:not([type=checkbox]){display:block;width:100%;min-height:49px;border-radius:13px!important;border:1px solid rgba(112,157,216,.25)!important;background:rgba(255,255,255,.65)!important;padding:12px 14px!important;margin:0 0 10px!important;box-shadow:inset 0 1px 2px rgba(14,54,107,.025);font-size:14px}
.rg-auth-form input:focus{outline:2px solid rgba(55,139,255,.45);outline-offset:1px}
.rg-pass{position:relative}.rg-pass input{padding-right:82px!important}
.rg-pass button{position:absolute;right:6px;top:6px;min-height:37px;background:rgba(66,139,241,.09)!important;color:var(--blue)!important;border-radius:10px;box-shadow:none!important;padding:8px 12px;font-size:11px}
.rg-remember{display:flex!important;gap:9px;align-items:center;font-weight:500!important;color:var(--muted);margin:14px 0!important;cursor:pointer}
.rg-remember input{width:16px;height:16px;accent-color:var(--blue);margin:0}
.rg-auth-form .rg-submit{width:100%;padding:13px 16px;margin-top:13px;min-height:49px;border-radius:13px;background:linear-gradient(115deg,#4998ff,#1769f4);box-shadow:0 9px 22px rgba(23,105,244,.22);font-size:14px}
.rg-auth-foot{text-align:center;color:var(--muted);font-size:11px;margin-top:21px;line-height:1.55;border-top:1px solid rgba(112,157,216,.14);padding-top:16px}
html[data-theme=dark] .rg-auth-card{background:linear-gradient(145deg,rgba(19,45,77,.93),rgba(12,34,63,.86))!important;border-color:rgba(112,157,216,.18)!important;box-shadow:0 16px 44px rgba(0,0,0,.18)!important}
html[data-theme=dark] .rg-auth-form input:not([type=checkbox]){background:rgba(8,28,53,.65)!important;color:var(--fg)}
html[data-theme=dark] .rg-auth-switch{background:rgba(102,160,234,.10)}
@media(max-width:520px){.rg-auth{margin:2px auto 35px}.rg-auth-back{margin-bottom:16px}.rg-auth-head{margin-bottom:17px}.rg-auth-head h1{font-size:29px}.rg-auth-card{padding:19px!important;border-radius:22px!important}.rg-auth-switch{margin-bottom:18px}.rg-auth-form h2{font-size:19px}}
@media(prefers-reduced-motion:reduce){.rg-auth *{transition:none!important}}
</style>
<main class="rg-auth">
<div class="rg-auth-head"><a href="/" class="rg-auth-back">← Beranda</a><span class="rg-auth-kicker">REKGO ACCOUNT</span><h1>Selamat datang kembali.</h1><p>Masuk atau buat akun untuk mengelola transaksi RekGo dengan mudah.</p></div>
<div class="glass rg-auth-card">
<div class="rg-auth-switch" role="tablist" aria-label="Pilih formulir"><button type="button" role="tab" id="tab-login" aria-controls="form-login" aria-selected="true" onclick="rgTab('login')">Masuk</button><button type="button" role="tab" id="tab-register" aria-controls="form-register" aria-selected="false" onclick="rgTab('register')">Daftar</button></div>
<form class="rg-auth-form" id="form-login" action="/login" method="post">@csrf<h2>Masuk ke akun</h2><p class="rg-auth-hint">Gunakan akun yang sudah terdaftar.</p><label for="identity">Email, username, atau WhatsApp</label><input id="identity" name="identity" autocomplete="username" value="{{ old('identity') }}" required><label for="login-password">Password</label><div class="rg-pass"><input id="login-password" type="password" name="password" autocomplete="current-password" required><button type="button" onclick="rgToggle('login-password',this)" aria-label="Tampilkan password">Lihat</button></div><label class="rg-remember"><input type="checkbox" name="remember" value="1" style="width:auto;margin:0"> Ingat saya</label><button class="rg-submit" type="submit">Masuk →</button></form>
<form class="rg-auth-form" id="form-register" action="/register" method="post" hidden>@csrf<h2>Buat akun RekGo</h2><p class="rg-auth-hint">Daftar dalam beberapa langkah mudah.</p><label for="name">Nama lengkap</label><input id="name" name="name" autocomplete="name" value="{{ old('name') }}" maxlength="100" required><label for="username">Username</label><input id="username" name="username" autocomplete="username" value="{{ old('username') }}" maxlength="50" required><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required><label for="phone">Nomor WhatsApp (wajib)</label><input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" value="{{ old('phone') }}" minlength="9" maxlength="20" pattern="\\+?[0-9]{9,16}" placeholder="Contoh: 6281234567890" required><label for="register-password">Password (minimal 12 karakter)</label><div class="rg-pass"><input id="register-password" type="password" name="password" autocomplete="new-password" minlength="12" required><button type="button" onclick="rgToggle('register-password',this)" aria-label="Tampilkan password">Lihat</button></div><label for="confirm-password">Ulangi password</label><div class="rg-pass"><input id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required><button type="button" onclick="rgToggle('confirm-password',this)" aria-label="Tampilkan ulangi password">Lihat</button></div><button class="rg-submit" type="submit">Buat akun →</button></form>
<div class="rg-auth-foot">Dilindungi untuk keamanan akun Anda.<br>Jangan bagikan password atau kode verifikasi.</div>
</div></main>
<script>
function rgTab(tab){for(const name of ['login','register']){const active=name===tab;document.getElementById('form-'+name).hidden=!active;document.getElementById('tab-'+name).setAttribute('aria-selected',String(active));}history.replaceState(null,'',tab==='register'?'#daftar':'/login');}
function rgToggle(id,btn){const input=document.getElementById(id);const show=input.type==='password';input.type=show?'text':'password';btn.textContent=show?'Tutup':'Lihat';btn.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');}
if(location.hash==='#daftar')rgTab('register');
</script>
@endsection