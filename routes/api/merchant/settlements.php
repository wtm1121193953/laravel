<?php
use Illuminate\Support\Facades\Route;

Route::get('/settlements', 'SettlementController@getList');
Route::get('/settlement/orders', 'SettlementController@getSettlementOrders');
Route::get('/settlement/download', 'SettlementController@download');

Route::get('/settlement/platform/list', 'SettlementPlatformController@getList');
Route::get('/settlement/platform/orders', 'SettlementPlatformController@getSettlementOrders');

