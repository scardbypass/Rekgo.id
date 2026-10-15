<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>RekGo • by CeirGo</title><link rel="manifest" href="/manifest.webmanifest"><link rel="stylesheet" href="{{ asset('css/rekgo-admin.css') }}"><meta name="theme-color" content="#1769f4"><style>
:root{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color-scheme:light;--bg:#eaf3ff;--fg:#0b2446;--muted:#62748b;--glass:#ffffffb8;--line:#ffffffc9;--blue:#1769f4;--shadow:0 15px 50px #3473bf1e}html[data-theme=dark]{color-scheme:dark;--bg:#061325;--fg:#f2f7ff;--muted:#a4bad4;--glass:#102d4bc7;--line:#41668b88;--blue:#4897ff;--shadow:0 15px 60px #0005}*{box-sizing:border-box}body{margin:0;min-height:100vh;background:radial-gradient(circle at 15% 0,#6caeff50,transparent 36%),radial-gradient(circle at 85% 20%,#a6b9ff50,transparent 30%),var(--bg);color:var(--fg)}.wrap{max-width:1060px;margin:auto;padding:20px 16px 90px}.glass{background:var(--glass);border:1px solid var(--line);border-radius:24px;box-shadow:var(--shadow);backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);padding:22px}.card{background:linear-gradient(135deg,#6bc4ff,#1b70ef 55%,#103b8d);color:white;border:2px solid #ffffff80;box-shadow:inset 0 2px 8px #ffffffa0,0 20px 38px #1463bb55;border-radius:30px;padding:30px;min-height:225px;position:relative;overflow:hidden}.card:after{content:'';position:absolute;inset:-80% -50% 35% 15%;transform:rotate(-15deg);border-radius:50%;background:linear-gradient(90deg,#fff0,#ffffff50,#fff0);pointer-events:none}.card h1{font-size:clamp(27px,5vw,40px);margin:16px 0 26px}.row{display:flex;justify-content:space-between;gap:12px;align-items:center}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:15px}.muted{color:var(--muted)}.small{font-size:13px}.pill{padding:5px 11px;border-radius:20px;background:#3589ef30;font-size:12px;font-weight:700}.success{color:#00996a}.pending{color:#c78305}.processing{color:#1d8cdd}.rejected{color:#e34d56}input,select,button,textarea{font:inherit}input,select,textarea{width:100%;padding:13px;border-radius:14px;border:1px solid var(--line);background:var(--glass);color:var(--fg);margin:6px 0 12px}button,.btn{background:var(--blue);border:0;color:white;border-radius:14px;padding:12px 17px;cursor:pointer;font-weight:650;text-decoration:none;display:inline-block}button.secondary,.btn.secondary{background:#6a8db03d;color:var(--fg)}button:disabled{opacity:.5;cursor:not-allowed}label{font-size:13px;font-weight:650}h2{font-size:21px}h3{font-size:16px}.stack>*+*{margin-top:15px}.tabs{display:flex;gap:10px;flex-wrap:wrap}.tabs a{color:var(--fg);text-decoration:none;padding:10px 13px;border-radius:14px;background:var(--glass)}table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:11px;border-bottom:1px solid var(--line);font-size:13px}.scroll{overflow-x:auto}.flash{background:#33b58125;padding:14px;border-radius:15px;margin:15px 0}.err{background:#f4433625;padding:14px;border-radius:15px;margin:15px 0}.nav{position:fixed;bottom:12px;left:50%;transform:translateX(-50%);width:min(96%,480px);display:flex;justify-content:space-around;background:var(--glass);backdrop-filter:blur(30px);border:1px solid var(--line);padding:10px;border-radius:25px;z-index:4}.nav a{text-decoration:none;color:var(--fg);font-size:12px}.logo{font-size:24px;font-weight:900;letter-spacing:-1px}.chip{display:inline-block;border-radius:15px;background:#ffffff70;color:#1257a9;padding:8px;font-weight:700}.card .muted{color:#e2efff}.card .row{align-items:end}.card .row div{min-width:0}.card .row strong{font-size:clamp(12px,2.3vw,16px)}.gap{height:14px}@media(max-width:520px){.card{padding:21px;min-height:210px}.glass{padding:17px}.wrap{padding:14px 12px 90px}}

/* RekGo dashboard design system */
:root{--radius:22px;--soft-border:rgba(74,113,161,.14);--surface:rgba(255,255,255,.88)}
html{scroll-behavior:smooth}body{font-size:15px;line-height:1.55}a,button{transition:background .18s,transform .18s,box-shadow .18s}a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid #60a5fa;outline-offset:2px}
.wrap{max-width:1180px;padding:25px 24px 115px}.glass{border:1px solid var(--soft-border);border-radius:var(--radius);box-shadow:0 10px 35px rgba(30,76,131,.07);background:var(--surface)}html[data-theme=dark] .glass{background:var(--glass);border-color:var(--line)}
.grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.grid>*{min-width:0}.row{min-width:0}.row>*{min-width:0}.tabs{padding:12px;gap:9px}.tabs a{font-weight:650;font-size:13px;padding:11px 15px;white-space:nowrap;background:rgba(50,121,229,.09)}.tabs a:hover{background:rgba(50,121,229,.18)}.scroll{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}table{min-width:520px}th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)}td,th{padding:14px 12px}tr:hover td{background:rgba(78,136,213,.04)}
.glass h2{margin-top:0;letter-spacing:-.03em}.glass h2:not(:first-child){margin-top:30px}.glass p{line-height:1.65}input,select,textarea{border-color:var(--soft-border);background:rgba(255,255,255,.68);min-height:46px}html[data-theme=dark] input,html[data-theme=dark] select,html[data-theme=dark] textarea{background:#122d49;color:var(--fg)}input[type=file]{padding:9px}button,.btn{min-height:43px}button:hover:not(:disabled),.btn:hover{transform:translateY(-1px)}.nav{box-shadow:0 10px 40px #0c305b25;padding:12px 10px}.nav a{font-weight:700;padding:4px 6px}.card{padding:30px}.card h1{overflow-wrap:anywhere;letter-spacing:-.045em}.card .row{flex-wrap:wrap}.card .row div{flex:1 1 110px}.card .row strong{overflow-wrap:anywhere}
.rg-page-heading{margin:4px 0 22px}.rg-page-heading h1{font-size:clamp(26px,4vw,37px);letter-spacing:-.045em;line-height:1.2;margin:4px 0}.rg-page-heading p{margin:5px 0;color:var(--muted)}.rg-section-label{font-size:13px;letter-spacing:.08em;text-transform:uppercase;font-weight:800;color:var(--muted);margin:26px 0 12px}.rg-dashboard-grid>section{align-self:start}.rg-dashboard-grid form button{max-width:100%}.rg-dashboard-grid .row{flex-wrap:wrap}.rg-balance-label{font-size:13px;opacity:.86}.rg-amount{font-size:clamp(28px,5vw,44px)!important;line-height:1.15;margin:8px 0 24px!important}.rg-stat-row{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.rg-stat-row>div{min-width:0}.rg-stat-row strong{display:block;font-size:clamp(12px,2vw,16px)!important;overflow-wrap:anywhere}.rg-history-section{margin-top:22px}.rg-history-section h2{padding-top:6px}.rg-history-section .scroll{margin-bottom:20px}
@media(max-width:760px){.wrap{padding:16px 13px 112px}.grid{grid-template-columns:1fr;gap:14px}.glass{padding:20px}.tabs{display:flex;overflow-x:auto;flex-wrap:nowrap;scrollbar-width:thin}.tabs a{flex:none}.card{padding:23px;min-height:0}.rg-stat-row{grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}.rg-stat-row strong{font-size:12px!important}.rg-page-heading h1{font-size:28px}.nav{bottom:10px;width:calc(100% - 18px);border-radius:20px}.nav a{font-size:11px}.row{gap:9px}}
@media(max-width:380px){.rg-stat-row{grid-template-columns:1fr}.rg-stat-row>div{display:flex;justify-content:space-between;gap:10px}.rg-stat-row strong{font-size:13px!important}.nav a{font-size:10px;padding:3px}}

/* RekGo UI v5 — compact liquid-glass design */
:root{--bg:#f4f7fc;--fg:#172c49;--muted:#6b7c92;--glass:rgba(255,255,255,.72);--line:rgba(112,148,194,.20);--blue:#1769f4;--shadow:0 8px 28px rgba(39,85,148,.075)}
body{background:radial-gradient(ellipse 70% 30% at 10% 0%,rgba(67,145,255,.15),transparent 90%),radial-gradient(ellipse 65% 30% at 95% 35%,rgba(120,160,255,.10),transparent 90%),var(--bg);letter-spacing:-.01em}
.wrap{max-width:1140px;padding-top:19px}
.wrap>header{padding:7px 0 13px;border-bottom:1px solid var(--line);margin-bottom:24px!important}
.logo{font-size:clamp(22px,3vw,27px);letter-spacing:-.05em}
.wrap>header .row{gap:8px}.wrap>header button{min-height:39px;padding:9px 13px;border-radius:13px;font-size:13px}
.glass{background:var(--glass);border:1px solid rgba(255,255,255,.84);box-shadow:0 6px 25px rgba(29,73,133,.075),inset 0 1px 0 rgba(255,255,255,.65);border-radius:21px;padding:23px;backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px)}
.glass h2{font-size:19px;font-weight:760;letter-spacing:-.035em;margin-bottom:12px}.glass h3{letter-spacing:-.025em}
.rg-page-heading{margin:6px 0 21px}.rg-page-heading h1{font-size:clamp(25px,3.5vw,34px);margin:6px 0 4px}.rg-page-heading p{font-size:13px;margin:4px 0}
.grid{gap:15px}.grid>.glass{align-self:start}
label{font-size:12px;font-weight:700;display:block;margin:15px 0 4px}
input:not([type=checkbox]),select,textarea{border:1px solid rgba(107,143,188,.23);border-radius:13px;background:rgba(255,255,255,.62);min-height:44px;padding:11px 13px;margin:4px 0 10px;box-shadow:inset 0 1px 2px rgba(34,69,117,.025);font-size:14px}
input[type=file]{min-height:44px;padding:9px!important;font-size:12px!important;max-width:100%}
button,.btn{font-size:13px;font-weight:740;border-radius:12px;padding:11px 16px;min-height:42px;box-shadow:0 3px 9px rgba(23,105,244,.10)}
button.secondary,.btn.secondary{background:rgba(75,126,196,.10);box-shadow:none}
button:disabled{box-shadow:none}
.glass form+form{margin-top:18px}.glass hr{border:0;border-top:1px solid var(--line);margin:20px 0}
.nav{bottom:calc(10px + env(safe-area-inset-bottom));width:min(96%,470px);border-radius:20px;padding:7px;box-shadow:0 9px 35px rgba(16,57,119,.17);background:rgba(255,255,255,.84);border:1px solid rgba(255,255,255,.92)}
.nav a{flex:1;text-align:center;border-radius:13px;padding:10px 4px;font-size:11px;font-weight:750;white-space:nowrap}
.nav a:hover{background:rgba(39,116,233,.09);color:var(--blue)}
.card{border-radius:24px;padding:25px;box-shadow:0 16px 35px rgba(17,88,196,.20)}
.card h1{font-size:clamp(28px,4.5vw,42px)}
table{min-width:490px}td,th{padding:11px 10px}th{font-size:11px}
html[data-theme=dark]{--glass:rgba(18,42,73,.78);--line:rgba(112,157,210,.19);--shadow:0 8px 28px rgba(0,0,0,.20)}
html[data-theme=dark] .glass{background:var(--glass);border-color:rgba(133,168,206,.15);box-shadow:0 8px 28px rgba(0,0,0,.20)}
html[data-theme=dark] .nav{background:rgba(18,42,73,.91);border-color:rgba(133,168,206,.18)}
html[data-theme=dark] input:not([type=checkbox]),html[data-theme=dark] select,html[data-theme=dark] textarea{background:rgba(11,30,53,.72);border-color:rgba(133,168,206,.22)}
@media(max-width:760px){.wrap{padding:12px 14px 105px}.wrap>header{margin-bottom:17px!important}.glass{padding:19px;border-radius:19px}.grid{gap:13px}.rg-page-heading{margin-bottom:18px}.rg-page-heading h1{font-size:27px}.rg-page-heading p{font-size:13px}.glass h2{font-size:18px}.nav{width:calc(100% - 24px)}.card{padding:21px}.card .rg-amount{font-size:31px!important}.wrap>header .row{flex-wrap:nowrap}.wrap>header button{padding:8px 11px}.logo{font-size:23px}}
@media(prefers-reduced-motion:reduce){a,button{transition:none}}
.rg-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.rg-quick-grid>a{display:flex;flex-direction:column;justify-content:center;min-height:130px;gap:4px;padding:20px}.rg-quick-grid>a h2{margin:4px 0 0}.rg-quick-grid>a p{margin:0;font-size:13px}.rg-quick-grid>a span{font-size:24px!important;line-height:1.1}@media(max-width:760px){.rg-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.rg-quick-grid>a{min-height:112px;padding:16px}.rg-quick-grid>a h2{font-size:16px}.rg-quick-grid>a p{font-size:12px}.rg-quick-grid>a:last-child:nth-child(odd){grid-column:1/-1}}
/* RekGo motion + responsive admin navigation */
@keyframes rgFadeRise{from{opacity:0;transform:translateY(17px) scale(.992)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes rgSheen{from{transform:translateX(-160%) rotate(16deg)}to{transform:translateX(230%) rotate(16deg)}}
@keyframes rgSoftFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
.wrap main>.rg-page-heading,.wrap>.rg-page-heading,.wrap>.card,.wrap>.glass,.wrap>.grid,.wrap>.rg-section-label,.wrap>section{animation:rgFadeRise .75s cubic-bezier(.22,1,.36,1) both}
.wrap>.grid{animation-delay:.1s}.wrap>section:nth-of-type(2){animation-delay:.16s}
.glass,.card{transition:transform .45s cubic-bezier(.22,1,.36,1),box-shadow .45s ease,border-color .4s ease}
.rg-admin-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
.rg-admin-grid>a{min-height:137px;display:flex;flex-direction:column;justify-content:center;gap:3px;color:var(--fg);text-decoration:none;position:relative;overflow:hidden}
.rg-admin-grid>a h2{font-size:17px;margin:4px 0 0}
.rg-admin-grid>a p{font-size:12px;margin:0}
.rg-admin-grid .rg-icon{display:grid;place-items:center;width:39px;height:39px;border-radius:12px;background:rgba(23,105,244,.1);color:var(--blue);font-size:23px}
.rg-admin-grid>a:hover,.rg-quick-grid>a:hover{transform:translateY(-4px);border-color:rgba(54,137,243,.3);box-shadow:0 18px 35px rgba(37,85,150,.13)}
.card:after{animation:rgSheen 11s ease-in-out infinite}
@media(max-width:760px){.rg-admin-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.rg-admin-grid>a{min-height:119px;padding:15px}.rg-admin-grid>a h2{font-size:15px}.rg-admin-grid>a p{font-size:11px}.rg-admin-grid .rg-icon{width:34px;height:34px;font-size:19px}}
@media(prefers-reduced-motion:reduce){.wrap main>.rg-page-heading,.wrap>.rg-page-heading,.wrap>.card,.wrap>.glass,.wrap>.grid,.wrap>.rg-section-label,.wrap>section{animation:none}.card:after{animation:none}.glass,.card{transition:none}}
.nav a[aria-current="page"]{background:rgba(23,105,244,.13);color:var(--blue);font-weight:800}.rg-admin-nav{width:min(96%,550px)}.rg-admin-nav a{flex:1;text-align:center}@media(max-width:430px){.rg-admin-nav{width:calc(100% - 16px);padding:6px}.rg-admin-nav a{font-size:10px;padding:9px 2px}}
/* RekGo account, payments and admin details — reusable UI primitives */
.rg-settings-grid{grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}
.rg-settings-grid>.glass,.rg-settings-grid>.card{height:100%}
.rg-avatar{display:grid;place-items:center;flex:none;width:58px;height:58px;border-radius:20px;background:linear-gradient(145deg,#89c9ff,#3477e9);color:#fff;font-size:24px;font-weight:800;box-shadow:0 10px 25px #1769f433}
.rg-records{display:flex;flex-direction:column}
.rg-record{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:16px 2px;border-bottom:1px solid var(--line)}
.rg-record:last-child{border-bottom:0}
.rg-record>div:first-child{min-width:0}
.rg-record small{display:block;color:var(--muted);font-size:12px;margin-top:4px}
.rg-record-right{display:flex;flex-direction:column;align-items:flex-end;gap:5px;white-space:nowrap}
.rg-record-right strong{font-variant-numeric:tabular-nums}
.rg-record a,.rg-user-link{color:var(--blue);text-decoration:none;font-weight:700}
.rg-notice{border-color:rgba(221,153,55,.3);margin-bottom:18px}
.rg-notice h2{margin-top:0}
.glass form{max-width:100%}
@media(max-width:700px){.rg-settings-grid{grid-template-columns:1fr}.rg-record{padding:14px 0}.rg-record-right{font-size:13px}.rg-avatar{width:48px;height:48px}}
</style><script>document.documentElement.dataset.theme=localStorage.getItem('rekgo-theme')||'light';function toggleTheme(){let n=document.documentElement.dataset.theme==='light'?'dark':'light';document.documentElement.dataset.theme=n;localStorage.setItem('rekgo-theme',n)}</script></head><body><div class="wrap"><header class="row" style="margin-bottom:20px"><div><span class="logo">💠 RekGo</span><div class="muted small">by CeirGo</div></div><div class="row"><button class="secondary" onclick="toggleTheme()" type="button">☀︎ / ☾</button>
@if(auth()->check())
<form action="/logout" method="post">
@csrf
<button class="secondary">Keluar</button>
</form>
@endif
</div></header>
@if(session('ok'))<div class="flash">{{session('ok')}}</div>
@endif
@if($errors->any())<div class="err">{{implode(' • ',$errors->all())}}</div>
@endif
@yield('content')
</div>

@if(auth()->check())
<nav class="nav {{ request()->is('admin') || request()->is('admin/*') ? 'rg-admin-nav' : '' }}" aria-label="Navigasi utama">
@if((request()->is('admin') || request()->is('admin/*')) && auth()->user()->isAdmin())
<a href="/admin" aria-label="Ringkasan admin">⌂ Ringkasan</a>
<a href="/admin/page/acc">↗ Pencairan</a>
<a href="/admin/page/users">♙ Pengguna</a>
<a href="/admin/page/fee">◇ Fee</a>
<a href="/dashboard">⇄ User</a>
@else
<a href="/dashboard">⌂ Beranda</a>
<a href="/transfer">↗ Transfer</a>
<a href="/history">◷ Riwayat</a>
<a href="/account">♙ Akun</a>
@if(auth()->user()->isAdmin())
<a href="/admin">⚙ Admin</a>
@endif
@endif
</nav>
@endif
<script>if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js').catch(()=>{}))}</script></body></html>