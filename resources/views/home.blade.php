@extends('layout')
@section('content')
<style>
.rg-home{--hblue:#4a9bff;overflow:hidden}.rg-home *{box-sizing:border-box}
.rg-home .rg-home-hero{display:grid;grid-template-columns:1.05fr .95fr;gap:34px;align-items:center;padding:65px 0 76px}
.rg-home .rg-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:850;letter-spacing:.14em;text-transform:uppercase;color:var(--blue);padding:9px 13px;border-radius:999px;background:rgba(67,142,255,.1);border:1px solid rgba(67,142,255,.18)}
.rg-home h1{font-size:clamp(39px,6vw,75px);line-height:1.04;letter-spacing:-.067em;margin:19px 0 18px;font-weight:900}
.rg-home .rg-gradient{background:linear-gradient(100deg,#2583ff,#65b8ff);background-clip:text;-webkit-background-clip:text;color:transparent}
.rg-home .rg-lead{font-size:clamp(15px,1.8vw,18px);line-height:1.8;color:var(--muted);max-width:600px}
.rg-home .rg-home-actions{display:flex;flex-wrap:wrap;gap:11px;margin-top:25px}
.rg-home .rg-home-actions a{display:inline-flex;justify-content:center;align-items:center;min-height:48px;padding:12px 19px;border-radius:15px;text-decoration:none;font-size:13px;font-weight:800;transition:transform .2s ease}
.rg-home .rg-primary{background:linear-gradient(115deg,#4a9bff,#1769f4);color:#fff!important;box-shadow:0 12px 25px rgba(23,105,244,.22)}
.rg-home .rg-secondary{color:var(--fg);border:1px solid var(--line);background:var(--glass)}
.rg-home .rg-home-actions a:hover{transform:translateY(-2px)}
.rg-home .rg-home-note{font-size:11px;color:var(--muted);line-height:1.6;margin:17px 0 0}
.rg-home .rg-preview{position:relative;min-width:0;padding:25px;border-radius:30px;background:linear-gradient(150deg,#123966,#071d39 65%,#102e54);color:white;box-shadow:0 30px 70px rgba(7,40,91,.25);border:1px solid rgba(143,201,255,.23)}
.rg-home .rg-preview:before{content:"";position:absolute;inset:0;border-radius:inherit;background:radial-gradient(circle at 80% 0,rgba(76,166,255,.25),transparent 53%);pointer-events:none}
.rg-home .rg-preview>*{position:relative}
.rg-home .rg-preview-top{display:flex;justify-content:space-between;align-items:center;gap:10px;padding-bottom:20px}
.rg-home .rg-preview-top strong{font-size:18px}.rg-home .rg-preview-top span{font-size:10px;color:#b8d8fa;border:1px solid #ffffff32;padding:7px 10px;border-radius:999px}
.rg-home .rg-chat{display:grid;gap:12px}
.rg-home .rg-bubble{border:1px solid rgba(172,213,255,.14);border-radius:17px;padding:13px 15px;max-width:95%;background:rgba(255,255,255,.08)}
.rg-home .rg-bubble.seller{margin-left:auto;background:rgba(58,141,251,.24);max-width:70%}
.rg-home .rg-bubble small{display:block;color:#9cc6f6;font-size:10px;margin-bottom:6px}
.rg-home .rg-bubble strong{font-size:13px}.rg-home .rg-bubble p{font-size:12px;color:#d4e8fc;margin:6px 0 0;line-height:1.5}
.rg-home .rg-preview-bottom{margin-top:20px;display:flex;align-items:center;gap:9px;font-size:11px;color:#b4d5f7}
.rg-home .rg-live-dot{height:8px;width:8px;background:#50dca5;border-radius:50%;box-shadow:0 0 0 4px #50dca51e}
.rg-home .rg-home-section{padding:38px 0 52px;scroll-margin-top:24px}
.rg-home .rg-section-head{max-width:720px;margin-bottom:26px}
.rg-home .rg-section-head h2{font-size:clamp(29px,4vw,44px);letter-spacing:-.055em;line-height:1.13;margin:12px 0}
.rg-home .rg-section-head p{font-size:14px;color:var(--muted);line-height:1.8}
.rg-home .rg-features{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
.rg-home .rg-feature{border-radius:22px;border:1px solid var(--line);background:var(--glass);padding:23px;box-shadow:0 12px 32px rgba(24,69,128,.055)}
.rg-home .rg-feature-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:15px;background:rgba(55,140,255,.12);font-size:23px;margin-bottom:16px}
.rg-home .rg-feature h3{font-size:16px;margin:0 0 8px}.rg-home .rg-feature p{font-size:12px;color:var(--muted);line-height:1.75;margin:0}
.rg-home .rg-steps{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.rg-home .rg-step{display:flex;gap:15px;align-items:flex-start;border:1px solid var(--line);background:var(--glass);border-radius:21px;padding:21px}
.rg-home .rg-step-num{display:grid;place-items:center;flex:none;width:39px;height:39px;border-radius:13px;background:rgba(52,135,251,.12);color:var(--blue);font-weight:900;font-size:13px}
.rg-home .rg-step h3{margin:2px 0 8px;font-size:16px}.rg-home .rg-step p{color:var(--muted);font-size:12px;line-height:1.75;margin:0}
.rg-home .rg-command{display:inline-block;margin:9px 0 3px;padding:9px 12px;border-radius:10px;background:rgba(45,132,250,.12);color:var(--blue);font-size:13px;font-weight:850;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.rg-home .rg-hours{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:19px}
.rg-home .rg-hours>div{padding:23px;border:1px solid var(--line);background:var(--glass);border-radius:21px}
.rg-home .rg-hours h3{font-size:17px;margin:0 0 9px}.rg-home .rg-hours p{font-size:12px;line-height:1.75;color:var(--muted);margin:0}
.rg-home .rg-promo{padding:34px;border-radius:26px;background:linear-gradient(125deg,#092f63,#1769f4);color:white;margin:24px 0 48px}
.rg-home .rg-promo h2{font-size:clamp(27px,4vw,42px);letter-spacing:-.05em;margin:8px 0 13px}
.rg-home .rg-promo p{color:#d9eaff;font-size:14px;line-height:1.8;max-width:680px}
.rg-home .rg-promo .rg-primary{background:white;color:#135bcb!important}
.rg-home .rg-final{text-align:center;padding:38px 20px 46px}
.rg-home .rg-final h2{font-size:clamp(30px,4vw,47px);letter-spacing:-.055em;margin:12px 0}
.rg-home .rg-final p{color:var(--muted);line-height:1.8;max-width:620px;margin:0 auto}
.rg-home .rg-final .rg-home-actions{justify-content:center}
.rg-home .rg-footer{text-align:center;border-top:1px solid var(--line);padding:23px 0 35px;color:var(--muted);font-size:11px;line-height:1.8}
@media(max-width:800px){.rg-home .rg-home-hero{grid-template-columns:1fr;gap:24px;padding:27px 0 44px}.rg-home .rg-preview{max-width:600px;width:100%;margin:auto}.rg-home .rg-features{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:540px){.rg-home h1{font-size:clamp(38px,10vw,49px)}.rg-home .rg-home-hero{padding-top:16px}.rg-home .rg-preview{padding:19px;border-radius:23px}.rg-home .rg-features,.rg-home .rg-steps,.rg-home .rg-hours{grid-template-columns:1fr}.rg-home .rg-feature,.rg-home .rg-step{padding:18px}.rg-home .rg-home-section{padding:30px 0 38px}.rg-home .rg-home-actions a{flex:1 1 auto}.rg-home .rg-promo{padding:25px 20px}.rg-home .rg-final{padding:26px 6px 40px}}
@media(prefers-reduced-motion:reduce){.rg-home .rg-home-actions a{transition:none}}
</style>
<main class="rg-home">
<section class="rg-home-hero" aria-labelledby="rg-home-title">
<div><span class="rg-eyebrow">💠 RekGo · by CeirGo</span><h1 id="rg-home-title">Rekber generasi baru.<br><span class="rg-gradient">Transaksi tanpa drama.</span></h1><p class="rg-lead">Mau jual beli online tapi takut kena tipu? Kenalan dengan RekGo, layanan rekening bersama modern dengan alur transaksi berbasis grup WhatsApp. Lebih praktis, transparan, dan mudah dipantau.</p><div class="rg-home-actions"><a class="rg-primary" href="/login">Mulai Transaksi Sekarang →</a><a class="rg-secondary" href="#cara-kerja">Lihat Cara Kerja ↗</a></div><p class="rg-home-note">RekGo — Your Trusted Transaction Partner. Selalu periksa detail dan ketentuan transaksi sebelum membayar.</p></div>
<div class="rg-preview" aria-label="Ilustrasi alur percakapan RekGo di WhatsApp"><div class="rg-preview-top"><strong>💠 RekGo</strong><span>ILUSTRASI BOT WHATSAPP</span></div><div class="rg-chat"><div class="rg-bubble seller"><small>Seller</small><strong>/rekgo 1000</strong></div><div class="rg-bubble"><small>RekGo Bot · Contoh pesan</small><strong>Tagihan transaksi dibuat</strong><p>Nominal: Rp1.000. Buyer silakan mengikuti instruksi pembayaran resmi di grup.</p></div><div class="rg-bubble"><small>RekGo Bot · Contoh status</small><strong>✓ Pembayaran terverifikasi</strong><p>Seller dapat melanjutkan proses setelah pembayaran benar-benar terkonfirmasi.</p></div></div><div class="rg-preview-bottom"><span class="rg-live-dot"></span>Alur bot WhatsApp yang direncanakan</div></div>
</section>
<section class="rg-home-section" id="keunggulan"><div class="rg-section-head"><span class="rg-eyebrow">KENAPA REKGO?</span><h2>Bukan rekber biasa. <span class="rg-gradient">Lebih modern, lebih praktis.</span></h2><p>RekGo menggabungkan alur rekening bersama, transparansi transaksi, dan otomatisasi WhatsApp dalam satu pengalaman.</p></div><div class="rg-features">
<article class="rg-feature"><span class="rg-feature-icon">🤖</span><h3>Otomatis via WhatsApp</h3><p>Rancangan transaksi melalui bot dalam grup, tanpa harus bolak-balik membuka website.</p></article>
<article class="rg-feature"><span class="rg-feature-icon">⚡</span><h3>Dirancang 24 Jam</h3><p>Bot dirancang menerima perintah dan mengirim notifikasi kapan saja setelah integrasi aktif.</p></article>
<article class="rg-feature"><span class="rg-feature-icon">🛡️</span><h3>Alur Perlindungan Dana</h3><p>Dana diproses melalui mekanisme rekening bersama sesuai verifikasi dan ketentuan layanan.</p></article>
<article class="rg-feature"><span class="rg-feature-icon">🔍</span><h3>Transparan & Terpantau</h3><p>Detail biaya, status, dan riwayat membantu kedua pihak mengikuti perkembangan transaksi.</p></article>
<article class="rg-feature"><span class="rg-feature-icon">📱</span><h3>Semua dari Genggaman</h3><p>WhatsApp untuk interaksi grup dan dashboard RekGo untuk mengakses akun.</p></article>
<article class="rg-feature"><span class="rg-feature-icon">💎</span><h3>Modern & Praktis</h3><p>Alur jelas, notifikasi terstruktur, dan proses yang mudah dipahami buyer maupun seller.</p></article>
</div></section>
<section class="rg-home-section" id="cara-kerja"><div class="rg-section-head"><span class="rg-eyebrow">CARA KERJA REKGO</span><h2>Rekber semudah <span class="rg-gradient">chat di WhatsApp.</span></h2><p>Enam langkah dari pendaftaran hingga penyelesaian. Alur bot berikut merupakan rancangan operasional dan bergantung pada kesiapan integrasi pembayaran serta WhatsApp.</p></div><div class="rg-steps">
<article class="rg-step"><span class="rg-step-num">01</span><div><h3>Daftar Akun RekGo</h3><p>Belum punya akun? Daftar terlebih dahulu di website RekGo. Jika sudah terdaftar, langsung masuk dan lanjutkan proses.</p></div></article>
<article class="rg-step"><span class="rg-step-num">02</span><div><h3>Buat Grup WhatsApp</h3><p>Seller membuat grup dengan nama bebas, lalu mengundang buyer dan bot resmi RekGo. Satu grup untuk koordinasi transaksi.</p></div></article>
<article class="rg-step"><span class="rg-step-num">03</span><div><h3>Seller Mulai Transaksi</h3><p>Seller mengetik perintah berikut. Bot dirancang membuat tagihan dan menampilkan instruksi pembayaran dalam grup.</p><code class="rg-command">/rekgo 1000</code></div></article>
<article class="rg-step"><span class="rg-step-num">04</span><div><h3>Buyer Melakukan Pembayaran</h3><p>Buyer membayar sesuai instruksi resmi. Setelah pembayaran benar-benar terverifikasi, bot mengirim status berhasil dan seller dapat melanjutkan proses.</p></div></article>
<article class="rg-step"><span class="rg-step-num">05</span><div><h3>Buyer Konfirmasi Selesai</h3><p>Setelah menerima hasil sesuai kesepakatan, buyer mengirim perintah berikut untuk meminta penyelesaian transaksi.</p><code class="rg-command">rekber done</code></div></article>
<article class="rg-step"><span class="rg-step-num">06</span><div><h3>Admin Verifikasi & Selesaikan</h3><p>Admin memeriksa keabsahan permintaan penyelesaian sebelum dana diteruskan kepada seller sesuai ketentuan layanan.</p></div></article>
</div>
<div class="rg-hours"><div><h3>🤖 Bot WhatsApp · Target 24/7</h3><p>Dirancang untuk pembuatan transaksi, instruksi pembayaran, verifikasi otomatis, dan notifikasi. Ketersediaan bergantung pada aktivasi integrasi.</p></div><div><h3>🕘 Admin · 09.00–20.00 WIB</h3><p>Admin memeriksa penyelesaian dan pencairan. Permintaan di luar jam kerja diproses pada hari operasional berikutnya.</p></div></div>
</section>
<section class="rg-promo"><span class="rg-eyebrow" style="background:#ffffff19;color:#d8edff;border-color:#ffffff2b">JUAL BELI ONLINE?</span><h2>Jangan modal percaya saja! 🔥</h2><p>Untuk transaksi gadget, elektronik, produk digital, dan berbagai jual beli online lainnya, pilih alur yang lebih jelas. Satu grup WhatsApp, satu sistem terintegrasi, dan konfirmasi yang tertata.</p><div class="rg-home-actions"><a class="rg-primary" href="/login">Coba RekGo Sekarang →</a></div></section>
<section class="rg-final"><span class="rg-eyebrow">REKGO · BY CEIRGO</span><h2>Deal sudah? <span class="rg-gradient">RekGo-in aja! 🚀</span></h2><p>Lebih praktis. Lebih transparan. Lebih nyaman. Mulai pengalaman transaksi rekening bersama modern bersama RekGo, bagian dari ekosistem CeirGo.</p><div class="rg-home-actions"><a class="rg-primary" href="/login">Mulai Transaksi →</a><a class="rg-secondary" href="/login#daftar">Masuk / Daftar ↗</a></div><p class="rg-home-note">Layanan mengikuti ketentuan, verifikasi pembayaran, dan prosedur keamanan yang berlaku. Sistem otomatis tidak menghilangkan seluruh risiko transaksi.</p></section>
<footer class="rg-footer">© {{ date('Y') }} RekGo • by CeirGo<br>RekGo — Transaksi Tenang, Deal Makin Gampang.</footer>
</main>
@endsection