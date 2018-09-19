<?php
use Illuminate\Support\Facades\Route;

Route::get('/bizer/getList', 'BizerController@getList');
Route::get('/bizer/detail', 'BizerController@detail');
Route::post('/bizer/changeStatus', 'BizerController@changeStatus');