<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiPartner\ApiPartnerController;


Route::middleware(['XSS','auth'])->prefix('api-partner')->group(function () {
    //api-partner Routes
    Route::get('/list',[ApiPartnerController::class, 'index'])->name('api-partner.list')->middleware('can:api-partner-list');
    Route::post('/ajax',[ApiPartnerController::class, 'showAll'])->name('api-partner.show.ajax')->middleware('can:api-partner-show');
    Route::get('/create',[ApiPartnerController::class, 'store'])->name('api-partner.create')->middleware('can:api-partner-create');
    Route::post('/store',[ApiPartnerController::class, 'store'])->name('api-partner.store')->middleware('can:api-partner-store');
    Route::get('/{apiPartner}',[ApiPartnerController::class, 'show'])->name('api-partner.show')->middleware('can:api-partner-show');
    Route::put('/{apiPartner}',[ApiPartnerController::class, 'update'])->name('api-partner.update')->middleware('can:api-partner-update');
    Route::get('/{apiPartner}/destroy',[ApiPartnerController::class, 'destroy'])->name('api-partner.destroy')->middleware('can:api-partner-destroy');
    Route::get('/{apiPartner}/edit',[ApiPartnerController::class, 'edit'])->name('api-partner.edit')->middleware('can:api-partner-edit');
    Route::get('/{apiPartner}/assign-permission',[ApiPartnerController::class, 'assginPermssion'])->name('api-partner.assginPermssion')->middleware('can:api-partner-edit');
    Route::post('/{apiPartner}/assign-permission',[ApiPartnerController::class, 'assginPermssionUpdate'])->name('api-partner.assginPermssion.post')->middleware('can:api-partner-edit');
    Route::post('/generate-username',[ApiPartnerController::class, 'generateUsername'])->middleware('can:api-partner-create');
    Route::get('/set-config/{user}',[ApiPartnerController::class, 'setConfig'])->name('api-partner-config')->middleware('can:api-partner-config');
    Route::post('/set-config/{user}',[ApiPartnerController::class, 'ConfigUpdate'])->name('api-partner-config.post')->middleware('can:api-partner-config');
    Route::get('transaction/list',[ApiPartnerController::class, 'transactionList'])->name('api-partner.transaction')->middleware('can:api-partner-transaction');
    Route::post('/transactions',[ApiPartnerController::class, 'transactionsAjax'])->name('api-partner.transactions.ajax')->middleware(middleware: 'can:api-partner-transaction');
    Route::post('/transactions/refund',[ApiPartnerController::class, 'initiateRefund'])->name('api-partner.refund')->middleware(middleware: 'can:api-partner-refund');
    Route::post('/transactions/query',[ApiPartnerController::class, 'getQuery'])->name('api-partner.query')->middleware(middleware: 'can:api-partner-query');
    Route::get('/transactions/ledger',[ApiPartnerController::class, 'ledger'])->name('api-partner.ledger')->middleware(middleware: 'can:api-partner-ledger');

    Route::post('/transaction/ledger/get',[ApiPartnerController::class, 'ledgerAjax'])->name('api-partner.ledger.ajax')->middleware(middleware: 'can:api-partner-transaction');

    Route::post('/pg-credentials/{pgCompany}/{user}',[ApiPartnerController::class, 'ApiPartnerPgCredentails'])->name('api-partner.pg.credentials')->middleware(middleware: 'can:api-partner-pg-credentials');
});