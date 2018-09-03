<?php
use Illuminate\Support\Facades\Route;

Route::get('/settlements', 'SettlementController@getList');

Route::get('/getSettlementOrders', 'SettlementController@getSettlementOrders');

Route::get('/getPlatformOrders', 'SettlementPlatformController@getSettlementOrders');

Route::get('/settlement/download', 'SettlementController@download');

Route::get('/settlement/platform', 'SettlementPlatformController@getList');
