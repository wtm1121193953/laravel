<?php
use Illuminate\Support\Facades\Route;

Route::get('/wallet/bill/list', 'WalletController@getBillList');
Route::get('/wallet/bill/exportExcel', 'WalletController@exportBillExcel');
Route::get('/wallet/bill/detail', 'WalletController@getBillDetail');

Route::get('/wallet/consume/list', 'WalletController@getConsumeQuotaList');
Route::get('/wallet/consume/detail', 'WalletController@getConsumeQuotaDetail');
Route::get('/wallet/consume/exportExcel', 'WalletController@exportConsumeQuotaRecordExcel');

Route::get('/wallet/withdraw/getPasswordInfo', 'WalletWithdrawController@getWalletPasswordInfo');
Route::post('/wallet/withdraw/setWalletPassword', 'WalletWithdrawController@setWalletPassword');