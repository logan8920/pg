<?php 

use App\Http\Controllers\Api\V1\PgtxnController;

Route::prefix('v1')->middleware('validate-ip')->group(function(){

    Route::post('/generate-token', [PgtxnController::class, 'generateToken'])->name('generate.token');
    Route::post('/generate-url', [PgtxnController::class, 'index']);

    Route::post('/pg-redirect', [PgtxnController::class, 'initiateTransaction'])->name('pg.redirect');
    
});


Route::post('/pg/{gateway}/callback',[PgtxnController::class, 'handlePgCallback'])->name("pg-redirecturl.callback");

Route::get('/pg-transaction-failure',function(){
    return view('payment-gateway.error');
})->name("pg-transaction.failure");

Route::get('/pg-transaction-limit',function(){
    return view('payment-gateway.error');
})->name("pg-transaction.limit");

//test 
Route::get('/pg/callback',[PgtxnController::class, 'handlePgCallbackTest'])->name("pg-redirecturl.callback.test");

Route::post('/client-return-url', function () {
    // Validate signature
    dd(request()->all());
})->name('client.return.url');