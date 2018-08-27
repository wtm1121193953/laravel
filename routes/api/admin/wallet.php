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

