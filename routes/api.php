<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekgoBotApiController;
Route::middleware('throttle:30,1')->prefix('rekgo-bot')->group(function(){
 Route::post('/escrows',[RekgoBotApiController::class,'create']);
 Route::post('/done',[RekgoBotApiController::class,'done']);
});
