<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

final class BankProviderSettingsController
{
    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->user()?->role === 'super_admin',403);
    }

    public function index(Request $request)
    {
        $this->authorizeOwner($request);
        $disk=Storage::disk('local');
        $settings=[];
        if($disk->exists('private/bank-inquiry-provider.enc')){
            try{$settings=json_decode(Crypt::decryptString($disk->get('private/bank-inquiry-provider.enc')),true)?:[];}
            catch(\Throwable $e){report($e);}
        }
        unset($settings['api_key'],$settings['password']);
        return view('admin-bank-provider',['settings'=>$settings,'has_key'=>$disk->exists('private/bank-inquiry-provider.enc')]);
    }

    public function save(Request $request)
    {
        $this->authorizeOwner($request);
        $d=$request->validate([
            'endpoint'=>'required|url|starts_with:https://|max:500',
            'method'=>'required|in:POST,GET',
            'provider_name'=>'required|string|max:100',
            'auth_type'=>'required|in:none,bearer,basic,header',
            'username'=>'nullable|string|max:200',
            'password'=>'nullable|string|max:2048',
            'auth_header'=>'nullable|string|max:100|regex:/^[A-Za-z0-9-]+$/',
            'auth_prefix'=>'nullable|string|max:50',
            'headers_json'=>'nullable|json|max:5000',
            'query_json'=>'nullable|json|max:5000',
            'api_key'=>'nullable|string|max:2048',
            'request_example'=>'required|json|max:5000',
            'response_example'=>'required|json|max:5000',
            'name_path'=>'required|string|regex:/^[A-Za-z0-9_.]+$/|max:100',
            'status_path'=>'required|string|regex:/^[A-Za-z0-9_.]+$/|max:100',
            'account_path'=>'nullable|string|regex:/^[A-Za-z0-9_.]+$/|max:100',
            'bank_path'=>'nullable|string|regex:/^[A-Za-z0-9_.]+$/|max:100',
            'success_value'=>'required|string|max:100',
            'error_path'=>'nullable|string|regex:/^[A-Za-z0-9_.]+$/|max:100',
        ]);
        // Configuration only. No arbitrary URL requests, secrets disclosure, or auto-approval.
        $disk=Storage::disk('local');$path='private/bank-inquiry-provider.enc';
        $old=[];
        if($disk->exists($path)){
            try{$old=json_decode(Crypt::decryptString($disk->get($path)),true)?:[];}
            catch(\Throwable $e){return back()->withErrors(['api_key'=>'Konfigurasi lama tidak dapat dibaca.']);}
        }
        $d['api_key']=$d['api_key']??$old['api_key']??'';
        $d['password']=$d['password']??$old['password']??'';
        if($d['auth_type']==='basic' && (!$d['username'] || !$d['password']))return back()->withErrors(['password'=>'Basic Auth membutuhkan username dan password.'])->withInput($request->except('password','api_key'));
        if(in_array($d['auth_type'],['bearer','header'],true) && !$d['api_key'])return back()->withErrors(['api_key'=>'Metode autentikasi ini membutuhkan API Key atau Token.'])->withInput($request->except('password','api_key'));
        $d['enabled']=false;
        $disk->put($path,Crypt::encryptString(json_encode($d,JSON_THROW_ON_ERROR)));
        \App\Models\Audit::create(['actor_id'=>$request->user()->id,'action'=>'bank.provider_configured','subject_type'=>'bank_provider','subject_id'=>0,'metadata'=>['endpoint_host'=>parse_url($d['endpoint'],PHP_URL_HOST),'enabled'=>false],'ip'=>$request->ip(),'created_at'=>now()]);
        return back()->with('ok','Template API bank tersimpan terenkripsi. Integrasi otomatis belum diaktifkan sampai adapter dan validasi respons diselesaikan.');
    }
}
