<?php
use Illuminate\Support\Facades\Route;

Route::get('/wallet/bill/list', 'WalletController@getBillList');
Route::get('/wallet/bill/exportExcel', 'WalletController@exportBillExcel');
Route::get('/wallet/bill/detail', 'WalletController@getBillDetail');

Route::get('/wallet/consume/list', 'WalletController@getConsumeQuotaList');
Route::get('/wallet/consume/detail', 'WalletController@getConsumeQuotaDetail');
Route::get('/wallet/consume/exportExcel', 'WalletController@exportConsumeQuotaRecordExcel');

Route::get('/wallet/tpsCredit/list', 'WalletController@getTpsCreditList');
Route::get('/wallet/tpsCredit/exportExcel', 'WalletController@exportTpsCreditExcel');
Route::get('/wallet/tpsCredit/detail', 'WalletController@getTpsCreditDetail');

Route::get('/wallet/withdraw/getPasswordInfo', 'WalletWithdrawController@getWalletPasswordInfo');
Route::post('/wallet/withdraw/setWalletPassword', 'WalletWithdrawController@setWalletPassword');
Route::get('/wallet/withdraw/getWithdrawInfo', 'WalletWithdrawController@getWithdrawInfoAndBankInfo');
Route::post('/wallet/withdraw/withdrawApplication', 'WalletWithdrawController@withdrawApplication');
Route::get('/wallet/withdraw/getWithdrawDetail', 'WalletWithdrawController@getWithdrawDetail');

Route::get('/wallet/bank/list', 'WalletController@getBankList');