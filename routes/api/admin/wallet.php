<?php
use Illuminate\Support\Facades\Route;

// 提现记录
Route::get('/withdraw/dashboard', 'WithdrawController@dashboard');
Route::get('/withdraw/records', 'WithdrawController@withdrawRecord');
Route::get('/withdraw/record/export', 'WithdrawController@exportExcel');
Route::get('/withdraw/record/detail', 'WithdrawController@withdrawDetail');
Route::post('/withdraw/record/audit', 'WithdrawController@audit');

// 提现批次管理
Route::get('/withdraw/batches', 'WalletBatchController@walletBatchList');

/*/withdraw/record/detail
/withdraw/record/audit
/withdraw/record/paysuccess
/withdraw/record/payfail
/withdraw/record/export*/
