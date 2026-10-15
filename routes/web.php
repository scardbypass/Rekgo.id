<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,UserController,AdminController};
Route::get('/',function(){return view('home',['publicFees'=>\App\Models\FeeTier::query()->where('scope','escrow')->where('active',true)->orderBy('minimum')->get()]);})->name('home');
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'loginForm'])->name('login');Route::post('/login',[AuthController::class,'login'])->middleware('throttle:8,1');Route::post('/register',[AuthController::class,'register'])->middleware('throttle:5,1');});
Route::middleware('auth')->group(function(){
 Route::post('/logout',[AuthController::class,'logout']);Route::get('/dashboard',[UserController::class,'dashboard']);Route::get('/transfer',[UserController::class,'transfer']);Route::get('/rekber',[UserController::class,'rekber']);Route::get('/qris',[UserController::class,'qrisPage']);Route::get('/history',[UserController::class,'history']);Route::get('/account',[UserController::class,'account']);Route::post('/profile/avatar',[UserController::class,'avatar']);Route::post('/profile/bank',[UserController::class,'bank']);Route::post('/profile/fee',[UserController::class,'settings']);Route::post('/withdrawals',[UserController::class,'withdraw'])->middleware('throttle:5,1');Route::post('/escrows',[UserController::class,'escrow'])->middleware('throttle:10,1');Route::post('/qris',[UserController::class,'qris'])->middleware('throttle:10,1');
 Route::middleware('admin')->prefix('admin')->group(function(){Route::get('/',[AdminController::class,'dashboard']);Route::get('/users/{user}',[AdminController::class,'userDetail'])->whereNumber('user');Route::post('/users/{user}/verify-bank',[AdminController::class,'verifyUserBank'])->whereNumber('user')->middleware('throttle:5,1');Route::post('/users/{user}/balance-adjust',[AdminController::class,'adjustBalance'])->whereNumber('user')->middleware('throttle:5,1');Route::get('/page/{page}',[AdminController::class,'page'])->where('page','acc|users|fee|admins|audits');Route::post('/withdrawals/{wd}/process',[AdminController::class,'process']);Route::post('/withdrawals/{wd}/success',[AdminController::class,'success']);Route::post('/withdrawals/{wd}/reject',[AdminController::class,'reject']);Route::get('/withdrawals/{wd}/proof',[AdminController::class,'proof']);Route::post('/admins',[AdminController::class,'addAdmin']);Route::post('/fees',[AdminController::class,'tier']);Route::post('/fees/{tier}/edit',[AdminController::class,'updateTier'])->whereNumber('tier');Route::post('/fees/{tier}/delete',[AdminController::class,'deleteTier'])->whereNumber('tier');});
});

Route::middleware('auth')->post('/announcements/{id}/dismiss',[\App\Http\Controllers\V4AdminController::class,'dismiss'])->whereNumber('id');
Route::middleware(['auth','admin'])->prefix('admin/v4')->group(function(){
 Route::get('/',[\App\Http\Controllers\V4AdminController::class,'settings']);
 Route::post('/registration',[\App\Http\Controllers\V4AdminController::class,'registration'])->middleware('throttle:5,1');
 Route::post('/maintenance',[\App\Http\Controllers\V4AdminController::class,'maintenance']);
 Route::post('/announcements',[\App\Http\Controllers\V4AdminController::class,'createAnnouncement']);
 Route::post('/announcements/{id}/toggle',[\App\Http\Controllers\V4AdminController::class,'toggleAnnouncement'])->whereNumber('id');
 Route::post('/announcements/{id}/delete',[\App\Http\Controllers\V4AdminController::class,'deleteAnnouncement'])->whereNumber('id');
});

Route::middleware(['auth','admin'])->prefix('admin/integrations')->group(function(){
 Route::get('/',[\App\Http\Controllers\IntegrationAdminController::class,'index']);
 Route::get('/bots',[\App\Http\Controllers\IntegrationAdminController::class,'bots']);
 Route::get('/gopay',[\App\Http\Controllers\IntegrationAdminController::class,'gopay']);
 Route::post('/bots/{id}/delete',[\App\Http\Controllers\IntegrationAdminController::class,'deleteBot'])->whereNumber('id');
 Route::post('/bots',[\App\Http\Controllers\IntegrationAdminController::class,'addBot']);
 Route::post('/bots/{id}/toggle',[\App\Http\Controllers\IntegrationAdminController::class,'toggleBot'])->whereNumber('id');
 Route::post('/pg',[\App\Http\Controllers\IntegrationAdminController::class,'savePg']);
 Route::post('/gopay/credentials',[\App\Http\Controllers\IntegrationAdminController::class,'saveGopayCredentials'])->middleware('throttle:5,1');
 Route::post('/gopay/qris-text',[\App\Http\Controllers\IntegrationAdminController::class,'saveQrisText'])->middleware('throttle:10,1');
});

Route::middleware(['auth','admin','throttle:5,1'])->prefix('admin/integrations')->group(function(){
 Route::post('/pg/otp/request',[\App\Http\Controllers\IntegrationAdminController::class,'requestOtp']);
 Route::post('/pg/otp/verify',[\App\Http\Controllers\IntegrationAdminController::class,'verifyOtp']);
});

Route::middleware('auth')->group(function(){
 Route::get('/qris/{payment}',[\App\Http\Controllers\QrisController::class,'show'])->name('qris.show');
 Route::get('/qris/{payment}/image.svg',[\App\Http\Controllers\QrisController::class,'image'])->name('qris.image');
});

Route::middleware(['auth','admin','throttle:10,1'])->group(function(){
 Route::get('/admin/integrations/gopay/live-test',[\App\Http\Controllers\GoPayLiveTestController::class,'show'])->name('admin.gopay.test');
 Route::post('/admin/integrations/gopay/live-test/check',[\App\Http\Controllers\GoPayLiveTestController::class,'check'])->name('admin.gopay.test.check');
 Route::get('/admin/integrations/gopay/live-test/image.svg',[\App\Http\Controllers\GoPayLiveTestController::class,'image'])->name('admin.gopay.test.image');
});

Route::middleware(['auth','admin','throttle:10,1'])->group(function(){
 Route::get('/admin/bank-provider',[\App\Http\Controllers\BankProviderSettingsController::class,'index']);
 Route::post('/admin/bank-provider',[\App\Http\Controllers\BankProviderSettingsController::class,'save']);
});

Route::middleware(['auth','admin'])->prefix('admin')->group(function(){
 Route::post('/bank-transfer-fee',[\App\Http\Controllers\AdminController::class,'bankTransferFee'])->middleware('throttle:5,1');
 Route::get('/banks',[\App\Http\Controllers\BankDirectoryController::class,'index']);
 Route::post('/banks',[\App\Http\Controllers\BankDirectoryController::class,'store'])->middleware('throttle:10,1');
 Route::post('/banks/{id}/rename',[\App\Http\Controllers\BankDirectoryController::class,'rename'])->whereNumber('id');
 Route::post('/banks/{id}/remove',[\App\Http\Controllers\BankDirectoryController::class,'remove'])->whereNumber('id');
 Route::post('/banks/{id}/restore',[\App\Http\Controllers\BankDirectoryController::class,'restore'])->whereNumber('id');
});

Route::middleware(['auth','admin','throttle:5,1'])->prefix('admin/users')->group(function(){
 Route::post('/{user}/contact',[\App\Http\Controllers\AdminUserAccountController::class,'contact'])->whereNumber('user');
 Route::post('/{user}/password',[\App\Http\Controllers\AdminUserAccountController::class,'password'])->whereNumber('user');
});
