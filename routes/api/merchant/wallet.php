<?php
use Illuminate\Support\Facades\Route;

Route::get('/wallet/bill/list', 'WalletController@getBillList');
Route::post('/wallet/bill/exportExcel', 'WalletController@exportExcel');