<?php
use Illuminate\Support\Facades\Route;

Route::get('/wallet/bill/list', 'WalletController@getBillList');
Route::get('/wallet/bill/exportExcel', 'WalletController@exportExcel');
Route::get('/wallet/bill/detail', 'WalletController@getBillDetail');