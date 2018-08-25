<?php
use Illuminate\Support\Facades\Route;

Route::get('/withdraw/dashboard', 'WithdrawController@dashboard');
Route::get('/withdraw/records', 'WithdrawController@withdrawRecord');
Route::get('/withdraw/record/export', 'WithdrawController@exportExcel');

/*/withdraw/record/detail
/withdraw/record/audit
/withdraw/record/paysuccess
/withdraw/record/payfail
/withdraw/record/export*/
