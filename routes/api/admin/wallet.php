<?php
use Illuminate\Support\Facades\Route;

// 提现记录
Route::get('/withdraw/dashboard', 'WithdrawController@dashboard');
Route::get('/withdraw/records', 'WithdrawController@withdrawRecord');
Route::get('/withdraw/record/export', 'WithdrawController@exportExcel');
Route::get('/withdraw/record/detail', 'WithdrawController@withdrawDetail');
Route::post('/withdraw/record/audit', 'WithdrawController@audit');
Route::post('/withdraw/record/paySuccess', 'WithdrawController@paySuccess');
Route::post('/withdraw/record/payFail', 'WithdrawController@payFail');

// 提现批次管理
Route::get('/withdraw/batches', 'WalletBatchController@walletBatchList');
Route::get('/withdraw/batch/export', 'WalletBatchController@exportExcel');
Route::post('/withdraw/batch/add', 'WalletBatchController@add');
Route::post('/withdraw/batch/delete', 'WalletBatchController@delete');
Route::get('/withdraw/batch/detail', 'WalletBatchController@detail');
Route::post('/withdraw/batch/changeStatus', 'WalletBatchController@changeBatchStatus');

Route::get('/wallet/list', 'WalletController@getWalletList');
Route::get('/wallet/list/export', 'WalletController@walletListExportExcel');
Route::post('/wallet/list/changeStatus', 'WalletController@changeWalletStatus');

Route::get('/wallet/bill/list', 'WalletController@getWalletBillList');
Route::get('/wallet/bill/exportExcel', 'WalletController@walletBillExportExcel');

Route::get('/wallet/bank/list', 'WalletController@getBankList');