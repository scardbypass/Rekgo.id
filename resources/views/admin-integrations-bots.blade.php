@extends('layout')
@section('content')
<div class="rg-page-heading"><p>RekGo • Admin Center</p><h1>WhatsApp Bot</h1><p>Kelola perangkat WhatsApp tanpa bercampur dengan pengaturan pembayaran.</p></div>
<p><a class="btn secondary" href="/admin/integrations">← Semua Integrasi</a></p>
<section class="glass"><h2>Tambah Bot</h2><p class="muted small">Setelah service bot-rekgo aktif di VPS, tekan Hubungkan untuk menampilkan QR atau meminta kode pairing.</p>
<form method="post" action="/admin/integrations/bots">@csrf
<label>Nama Bot</label><input name="label" required maxlength="100" placeholder="RekGo Utama">
<label>Nomor WhatsApp (62...)</label><input name="phone" required pattern="62[0-9]{8,13}" placeholder="6281234567890">
<label>Metode koneksi</label><select name="connection_method"><option value="qr">QR Code</option><option value="pair">Pairing Code</option></select>
<button type="submit">Daftarkan Bot</button></form></section>
<div class="gap"></div><section class="glass"><h2>Bot Terdaftar</h2>
@forelse($bots as $bot)
<div style="padding:16px 0;border-bottom:1px solid var(--line)">
<div class="row"><div><strong>{{ $bot->label }}</strong><p class="muted small">{{ $bot->phone }} · {{ $bot->connection_method }}</p></div><span class="pill">{{ $bot->status }}</span></div>
<p class="muted small">{{ $bot->enabled ? 'Aktif' : 'Nonaktif' }}</p>
<div class="row" style="gap:8px;flex-wrap:wrap">
<button type="button" class="secondary" onclick="rekgoConnect({{ $bot->id }})">Hubungkan</button>
<form method="post" action="/admin/integrations/bots/{{ $bot->id }}/pair">@csrf<button type="submit" class="secondary">Kode Pairing</button></form>

<form method="post" action="/admin/integrations/bots/{{ $bot->id }}/toggle">@csrf<input type="hidden" name="enabled" value="{{ $bot->enabled ? 0 : 1 }}"><button type="submit" class="secondary">{{ $bot->enabled ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
@if($bot->status !== 'online')
<form method="post" action="/admin/integrations/bots/{{ $bot->id }}/delete" onsubmit="return confirm('Hapus bot ini dari RekGo? Pastikan sesi bridge WhatsApp sudah diputus.')">@csrf<button type="submit" class="secondary" style="color:#dc3545">Hapus Bot</button></form>
@else <span class="muted small">Putuskan koneksi bot sebelum menghapus.</span> @endif
</div>
<div id="rekgo-connect-{{ $bot->id }}" style="display:none;margin-top:14px;padding:14px;border-radius:16px;background:rgba(125,155,200,.10)">
 <p class="muted small" id="rekgo-status-{{ $bot->id }}">Menghubungi service WhatsApp...</p>
 <img id="rekgo-qr-{{ $bot->id }}" alt="QR WhatsApp" style="display:none;width:min(100%,270px);border-radius:14px;background:white;padding:10px;margin:12px auto">
 <button type="button" class="secondary" onclick="rekgoConnect({{ $bot->id }})">Perbarui QR / Status</button>
</div>
@if(session('pairing_bot_id')==$bot->id && session('pairing_code'))
<div class="glass" style="margin-top:10px;padding:15px"><p>Kode pairing WhatsApp:</p><strong style="font-size:24px;letter-spacing:3px">{{ session('pairing_code') }}</strong><p class="muted small">Masukkan kode ini pada WhatsApp → Perangkat tertaut. Jangan bagikan ke orang lain.</p></div>
@endif
</div>
@empty<p class="muted">Belum ada bot terdaftar.</p>@endforelse
</section>
<script>
async function rekgoConnect(id){
 const panel=document.getElementById('rekgo-connect-'+id);
 const status=document.getElementById('rekgo-status-'+id);
 const img=document.getElementById('rekgo-qr-'+id);
 panel.style.display='block';status.textContent='Memeriksa koneksi...';img.style.display='none';
 try{
  const response=await fetch('/admin/integrations/bots/'+id+'/status',{credentials:'same-origin',headers:{'Accept':'application/json'}});
  const data=await response.json();
  if(!response.ok){status.textContent=data.message||'Service WhatsApp belum aktif';return;}
  const states={online:'WhatsApp terhubung',awaiting_scan:'Scan QR melalui WhatsApp → Perangkat tertaut',connecting:'Menghubungkan ke WhatsApp...',offline:'Bot offline. Jalankan service bot-rekgo di VPS.'};
  status.textContent=states[data.connection]||data.connection;
  if(data.qr_image){img.src=data.qr_image;img.style.display='block';}
 }catch{status.textContent='Tidak dapat mengambil status koneksi.';}
}
</script>
@endsection