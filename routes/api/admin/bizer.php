<?php
use Illuminate\Support\Facades\Route;

Route::get('/bizer/getList', 'BizerController@getList');
Route::get('/bizer/detail', 'BizerController@detail');
Route::get('/bizer/export', 'BizerController@exportExcel');
Route::post('/bizer/changeStatus', 'BizerController@changeStatus');
Route::post('/bizer/identity/audit', 'BizerController@bizerIdentityAudit');