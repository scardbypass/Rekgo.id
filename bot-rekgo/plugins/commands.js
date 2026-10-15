// Command registry. Financial actions remain validated by Laravel API.
const definitions = [
  {name:'akun', aliases:['/akun','akun'], description:'Lihat saldo, bank, dan total transaksi'},
  {name:'rekgo', aliases:['/rekgo'], description:'Buat rekber: /rekgo 10000'},
  {name:'done', aliases:['rekber done'], description:'Buyer meminta pemeriksaan admin'},
  {name:'menu', aliases:['/menu','menu','/start','start'], description:'Daftar perintah'},
  {name:'help', aliases:['/help','help','bantuan','/bantuan'], description:'Panduan penggunaan'}
];
export function parseCommand(input) {
 const text=String(input||'').trim();
 const payment=text.match(/^\/rekgo\s+(\d{1,10})$/i);
 if(payment)return {name:'rekgo',amount:Number(payment[1])};
 for(const entry of definitions) {
  if(entry.name==='rekgo')continue;
  if(entry.aliases.some(alias=>alias.toLowerCase()===text.toLowerCase()))return {name:entry.name};
 }
 return null;
}
export function menuText(){
 return ['💠 *RekGo • Menu Bot*','',...definitions.map(x=>'• '+x.description),'','Layanan admin: 09.00–20.00 WIB.','Pembayaran dan pencairan hanya diproses setelah verifikasi.'].join('\n');
}
export function helpText(){
 return ['💠 *Panduan RekGo*','','1. Daftar akun di https://rekgo.my.id','2. Seller buat grup WhatsApp berisi buyer dan bot','3. Seller ketik /rekgo 10000','4. Tunggu instruksi pembayaran yang sah','5. Setelah pembayaran diverifikasi dan transaksi selesai, buyer ketik rekber done','6. Admin memeriksa permintaan sesuai jam kerja 09.00–20.00 WIB','','Bot tidak memproses nomor yang belum terdaftar.'].join('\n');
}
