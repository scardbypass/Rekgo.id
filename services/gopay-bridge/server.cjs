'use strict';
// Local-only GMA SDK bridge. Install licensed SDK separately; never publish its code.
const http=require('node:http');
const crypto=require('node:crypto');
const fs=require('node:fs');
const path=require('node:path');
const {GMAClient}=require(process.env.GMA_SDK_PATH || './licensed-sdk/gma.js');
const port=Number(process.env.BRIDGE_PORT||4319);
const key=process.env.BRIDGE_KEY;
const outlet=process.env.GMA_OUTLET_ID;
if(!key||key.length<32||!outlet) throw Error('BRIDGE_KEY (>=32 chars) and GMA_OUTLET_ID required');
const tokenFile=process.env.GMA_TOKEN_FILE||path.join(__dirname,'private-tokens.json');
function loadTokens(){try{return JSON.parse(fs.readFileSync(tokenFile,'utf8'))}catch(e){if(e.code==='ENOENT')return {};throw e}}
function saveTokens(tokens){const current=loadTokens();const data={...current,...tokens};fs.writeFileSync(tokenFile+'.tmp',JSON.stringify(data),{mode:0o600});fs.renameSync(tokenFile+'.tmp',tokenFile);fs.chmodSync(tokenFile,0o600)}
const saved=loadTokens();
const client=new GMAClient({accessToken:saved.accessToken||saved.access_token,refreshToken:saved.refreshToken||saved.refresh_token,outletId:outlet,onTokenChange:saveTokens});
const allowed=new Set(['create','status','cancel','login-request','login-verify']);
const respond=(res,code,data)=>{res.writeHead(code,{'Content-Type':'application/json','Cache-Control':'no-store'});res.end(JSON.stringify(data))};
http.createServer(async(req,res)=>{
 if(req.method!=='POST'||req.url!=='/v1/action')return respond(res,404,{error:'not_found'});
 const auth=req.headers.authorization||'';const a=Buffer.from(auth),b=Buffer.from('Bearer '+key);
 if(a.length!==b.length||!crypto.timingSafeEqual(a,b))return respond(res,401,{error:'unauthorized'});
 let raw='';for await(const chunk of req){raw+=chunk;if(raw.length>32768)return respond(res,413,{error:'too_large'})}
 try{
 const data=JSON.parse(raw);if(!allowed.has(data.action))return respond(res,422,{error:'unsupported_action'});
 let result;
 switch(data.action){
 case 'create': if(!Number.isSafeInteger(data.amount)||data.amount<1000||data.amount>1000000000)throw Error('invalid_amount');result=await client.createQRIS({grossAmount:data.amount,label:String(data.label||'').slice(0,40),outletId:outlet});break;
 case 'status': if(!/^[\w.-]{1,150}$/.test(data.reference||''))throw Error('invalid_reference');result=await client.getTransactionStatus(data.reference,{outletId:outlet});break;
 case 'cancel': if(!/^[\w.-]{1,150}$/.test(data.reference||''))throw Error('invalid_reference');result=await client.cancelTransaction(data.reference,{outletId:outlet});break;
 case 'login-request':result=await client.requestLogin(data.phone);break;
 case 'login-verify':result=await client.authenticate(data.otp_token,data.otp);saveTokens({accessToken:client.accessToken,refreshToken:client.refreshToken});break;
 }
 return respond(res,200,{ok:true,result});
 }catch(e){return respond(res,502,{ok:false,error:'provider_error',message:String(e.message||'Request failed').slice(0,180)})}
}).listen(port,'127.0.0.1',()=>process.stdout.write('GMA bridge listening on localhost:'+port+'\n'));
