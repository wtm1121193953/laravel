<?php
use Illuminate\Support\Facades\Route;

Route::get('/wallet/bill/list', 'WalletController@getBillList');
Route::get('/wallet/bill/exportExcel', 'WalletController@exportBillExcel');
Route::get('/wallet/bill/detail', 'WalletController@getBillDetail');

Route::get('/wallet/bank/list', 'WalletController@getBankList');

Route::post('/wallet/withdraw/setting', 'WalletWithdrawController@withdrawSetting');
Route::get('/wallet/withdraw/getRecordAndWallet', 'WalletWithdrawController@getBizerIdentityAuditRecordAndWalletInfo');
Route::post('/wallet/withdraw/addBizerIdentityAuditRecord', 'WalletWithdrawController@addBizerIdentityAuditRecord');
Route::post('/wallet/withdraw/editBizerIdentityAuditRecord', 'WalletWithdrawController@editBizerIdentityAuditRecord');
Route::get('/wallet/withdraw/getBankCardAndIdCardInfo', 'WalletWithdrawController@getBankCardAndIdCardInfo');

Route::get('/wallet/withdraw/getWithdrawInfoAndBankInfo', 'WalletWithdrawController@getWithdrawInfoAndBankInfo');
Route::post('/wallet/withdraw/withdraw', 'WalletWithdrawController@withdrawApplication');
Route::post('/wallet/withdraw/resetWithdrawPassword', 'WalletWithdrawController@resetWithdrawPassword');