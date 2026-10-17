import 'dotenv/config';
import * as Baileys from '@whiskeysockets/baileys';
const makeWASocket = Baileys.makeWASocket ?? Baileys.default?.makeWASocket ?? Baileys.default?.default ?? Baileys.default;
const {useMultiFileAuthState,DisconnectReason} = Baileys;
import pino from 'pino';
import http from 'node:http';
import QRCode from 'qrcode';
import {parseCommand,menuText,helpText} from './plugins/commands.js';
const base=process.env.REKGO_API_URL,key=process.env.REKGO_BOT_API_KEY;
if(!base||!key||key.length<32)throw Error('Configure REKGO_API_URL and REKGO_BOT_API_KEY');
const authDir=process.env.REKGO_AUTH_DIR||'./auth';
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
const bridge={connection:'offline',qr:null,pairingCode:null,updatedAt:null};
let currentSocket=null;
const listenPort=Number(process.env.REKGO_BOT_CONTROL_PORT||4320);
const control=http.createServer(async(req,res)=>{
 const send=(code,body)=>{res.writeHead(code,{'content-type':'application/json','cache-control':'no-store'});res.end(JSON.stringify(body));};
 if(req.headers.authorization!=='Bearer '+key)return send(401,{error:'unauthorized'});
 if(req.method==='GET'&&req.url==='/status'){
  let qrImage=null;
  if(bridge.qr){try{qrImage=await QRCode.toDataURL(bridge.qr,{margin:1,width:280});}catch{}}
  return send(200,{connection:bridge.connection,qr_image:qrImage,updated_at:bridge.updatedAt});
 }
 if(req.method==='POST'&&req.url==='/pair'){
  if(!currentSocket || bridge.connection==='offline')return send(503,{error:'Koneksi WhatsApp belum siap; periksa log bot'});
  if(bridge.connection==='online')return send(409,{error:'Bot sudah terhubung'});
  let raw='';for await(const part of req){raw+=part;if(raw.length>2048)return send(413,{error:'Too large'});}
  let phone;try{phone=JSON.parse(raw).phone;}catch{return send(422,{error:'Invalid JSON'});}
  if(typeof phone!=='string'||!/^62[0-9]{8,13}$/.test(phone))return send(422,{error:'Nomor harus 62...'});
  try{const code=await currentSocket.requestPairingCode(phone);bridge.pairingCode=code;return send(200,{code});}
  catch(e){return send(502,{error:'Pairing gagal, periksa status koneksi dan coba lagi'});}
 }
 return send(404,{error:'Not found'});
});
control.listen(listenPort,'127.0.0.1',()=>console.log('RekGo WhatsApp control ready on 127.0.0.1:'+listenPort));

async function api(path,payload){
 const response=await fetch(new URL(path,base),{method:'POST',headers:{'authorization':'Bearer '+key,'content-type':'application/json','accept':'application/json'},body:JSON.stringify(payload),signal:AbortSignal.timeout(15000)});
 const body=await response.json().catch(()=>({}));
 if(!response.ok){const error=new Error(body.message||'API error '+response.status);error.status=response.status;throw error;}
 return body;
}
async function run(){
 const {state,saveCreds}=await useMultiFileAuthState(authDir);
 if(typeof makeWASocket!=='function') throw Error('Baileys socket export unavailable');
 const sock=makeWASocket({auth:state,logger:pino({level:'warn'})});
 currentSocket=sock;
 if(process.env.REKGO_PAIR_PHONE && !state.creds.registered){
  const phone=process.env.REKGO_PAIR_PHONE.replace(/\D/g,'');
  if(!/^62[0-9]{8,13}$/.test(phone))throw Error('REKGO_PAIR_PHONE must start with 62');
  setTimeout(async()=>{
   try{const code=await sock.requestPairingCode(phone);console.log('WhatsApp pairing code:',code);}
   catch(error){console.error('Pairing request failed:',error.message);}
  },2500);
 }
 sock.ev.on('creds.update',saveCreds);
 sock.ev.on('connection.update',({connection,lastDisconnect,qr})=>{
  if(qr){bridge.qr=qr;bridge.connection='awaiting_scan';bridge.updatedAt=new Date().toISOString();console.log('WhatsApp QR ready; open RekGo Admin → Integrations → Bots');}
  if(connection==='connecting'){bridge.connection='connecting';bridge.updatedAt=new Date().toISOString();}
  if(connection==='open'){bridge.connection='online';bridge.qr=null;bridge.pairingCode=null;bridge.updatedAt=new Date().toISOString();console.log('WhatsApp connected');}
  if(connection==='close'){bridge.connection='offline';bridge.qr=null;bridge.updatedAt=new Date().toISOString();console.warn('WhatsApp disconnected',lastDisconnect?.error?.output?.statusCode||'unknown');}
  if(connection==='close'){
   const code=lastDisconnect?.error?.output?.statusCode;
   if(code===405){console.error('WA rejected registration (405). Check Baileys compatibility and session; exiting to prevent stale socket.');process.exitCode=1;control.close();return;}
   if(code!==DisconnectReason.loggedOut)setTimeout(run,10000);
  }
 });
 sock.ev.on('messages.upsert',async({messages,type})=>{
  if(type!=='notify')return;
  for(const m of messages){
   if(m.key.fromMe||!m.key.remoteJid?.endsWith('@g.us'))continue;
   const content=m.message?.conversation||m.message?.extendedTextMessage?.text||'';
   const text=content.trim();
   const sender=m.key.participant||'';
   const group=m.key.remoteJid;
   const command=parseCommand(text);
   if(!command)continue;
   const match=command.name==='rekgo' ? [null,String(command.amount)] : null;
   const account=command.name==='akun';
   try{
    if(command.name==='menu'||command.name==='help'){
     const phone=sender.split('@')[0].replace(/\D/g,'');
     if(!phone)continue;
     await api('/api/rekgo-bot/account',{phone});
     await sock.sendMessage(sender,{text:command.name==='menu'?menuText():helpText()});
    }else if(account){
     const phone=sender.split('@')[0].replace(/\D/g,'');
     if(!phone)continue;
     const data=await api('/api/rekgo-bot/account',{phone});
     const rupiah=n=>'Rp'+Number(n||0).toLocaleString('id-ID');
     const lines=['💠 RekGo • Account Center','Nama: '+data.name,'Username: @'+data.username,'','Saldo tersedia: '+rupiah(data.balance.available),'Saldo tertunda: '+rupiah(data.balance.pending),'Saldo terkunci: '+rupiah(data.balance.locked),'Total saldo: '+rupiah(data.balance.total),'','Bank: '+data.bank.name,'Rekening: '+data.bank.account_masked,'Status: '+(data.bank.verified?'Terverifikasi':'Belum diverifikasi'),'','Total transaksi: '+data.transactions.total,'WD tertunda: '+data.withdrawals.pending,'Total WD: '+data.withdrawals.total,'WD berhasil: '+rupiah(data.withdrawals.total_paid)];
     await sock.sendMessage(sender,{text:lines.join('\n')});
    }else if(match){
     const phone=sender.split('@')[0].replace(/\D/g,'');
     if(!phone)throw Error('Nomor seller tidak tersedia; gunakan akun WhatsApp bernomor telepon.');
     const data=await api('/api/rekgo-bot/escrows',{request_id:m.key.id,group_jid:group,seller_jid:sender,seller_phone:phone,amount:Number(match[1])});
     await sock.sendMessage(group,{text:'💠 RekGo • Transaksi #'+data.id+'\nNominal: Rp'+Number(data.amount).toLocaleString('id-ID')+'\nBiaya layanan: Rp'+Number(data.fee).toLocaleString('id-ID')+'\nStatus: Menunggu pembayaran\n\n⚠️ Jangan transfer dana sebelum instruksi pembayaran resmi tersedia dan diverifikasi.'});
    }else{
     const buyerPhone=sender.split('@')[0].replace(/\D/g,'');
     if(!buyerPhone)throw Error('Nomor WhatsApp tidak tersedia.');
     const data=await api('/api/rekgo-bot/done',{group_jid:group,buyer_jid:sender,buyer_phone:buyerPhone});
     await sock.sendMessage(group,{text:'RekGo #'+data.id+': permintaan selesai diterima untuk pemeriksaan admin. Dana belum dicairkan.'});
    }
   }catch(e){if(e.status===403)continue;if(account||command.name==='menu'||command.name==='help')console.warn('Account lookup failed:',e.message);else await sock.sendMessage(group,{text:'RekGo: '+String(e.message).slice(0,240)});}
   await sleep(250);
  }
 });
}
run().catch(e=>{console.error(e);process.exit(1)});
