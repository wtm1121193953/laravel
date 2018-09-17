<?php
use Illuminate\Support\Facades\Route;

Route::get('/wallet/bill/list', 'WalletController@getBillList');
Route::get('/wallet/bill/exportExcel', 'WalletController@exportBillExcel');
Route::get('/wallet/bill/detail', 'WalletController@getBillDetail');

Route::get('/wallet/bank/list', 'WalletController@getBankList');

Route::post('/wallet/withdraw/setting', 'WalletWithdrawController@withdrawSetting');