import 'dotenv/config';
import makeWASocket,{useMultiFileAuthState,DisconnectReason} from '@whiskeysockets/baileys';
import pino from 'pino';
const base=process.env.REKGO_API_URL,key=process.env.REKGO_BOT_API_KEY;
if(!base||!key||key.length<32)throw Error('Configure REKGO_API_URL and REKGO_BOT_API_KEY');
const authDir=process.env.REKGO_AUTH_DIR||'./auth';
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
async function api(path,payload){
 const response=await fetch(new URL(path,base),{method:'POST',headers:{'authorization':'Bearer '+key,'content-type':'application/json','accept':'application/json'},body:JSON.stringify(payload),signal:AbortSignal.timeout(15000)});
 const body=await response.json().catch(()=>({}));
 if(!response.ok){const error=new Error(body.message||'API error '+response.status);error.status=response.status;throw error;}
 return body;
}
async function run(){
 const {state,saveCreds}=await useMultiFileAuthState(authDir);
 const sock=makeWASocket({auth:state,logger:pino({level:'warn'}),printQRInTerminal:true});
 sock.ev.on('creds.update',saveCreds);
 sock.ev.on('connection.update',({connection,lastDisconnect})=>{
  if(connection==='close' && lastDisconnect?.error?.output?.statusCode!==DisconnectReason.loggedOut)setTimeout(run,3000);
 });
 sock.ev.on('messages.upsert',async({messages,type})=>{
  if(type!=='notify')return;
  for(const m of messages){
   if(m.key.fromMe||!m.key.remoteJid?.endsWith('@g.us'))continue;
   const content=m.message?.conversation||m.message?.extendedTextMessage?.text||'';
   const text=content.trim();
   const sender=m.key.participant||'';
   const group=m.key.remoteJid;
   const match=text.match(/^\/rekgo\s+(\d{1,10})$/i);
   const done=/^rekber\s+done$/i.test(text);
   if(!match&&!done&&!account)continue;
   try{
    if(account){
     const phone=sender.split('@')[0].replace(/\\D/g,'');
     if(!phone)continue;
     const data=await api('/api/rekgo-bot/account',{phone});
     const rupiah=n=>'Rp'+Number(n||0).toLocaleString('id-ID');
     const lines=['💠 RekGo • Account Center','Nama: '+data.name,'Username: @'+data.username,'','Saldo tersedia: '+rupiah(data.balance.available),'Saldo tertunda: '+rupiah(data.balance.pending),'Saldo terkunci: '+rupiah(data.balance.locked),'Total saldo: '+rupiah(data.balance.total),'','Bank: '+data.bank.name,'Rekening: '+data.bank.account_masked,'Status: '+(data.bank.verified?'Terverifikasi':'Belum diverifikasi'),'','Total transaksi: '+data.transactions.total,'WD tertunda: '+data.withdrawals.pending,'Total WD: '+data.withdrawals.total,'WD berhasil: '+rupiah(data.withdrawals.total_paid)];
     await sock.sendMessage(sender,{text:lines.join('\\n')});
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
   }catch(e){if(account && e.status===403)continue;if(account)console.warn('Account lookup failed:',e.message);else await sock.sendMessage(group,{text:'RekGo: '+String(e.message).slice(0,240)});}
   await sleep(250);
  }
 });
}
run().catch(e=>{console.error(e);process.exit(1)});
