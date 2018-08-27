<?php
use Illuminate\Support\Facades\Route;

Route::get('/settlements', 'SettlementController@getList');

Route::get('/getSettlementOrders', 'SettlementController@getSettlementOrders');

// Author:Jerry Date:180825
Route::get('/getPlatformOrders', 'SettlementPlatformController@getSettlementOrders');

Route::get('/settlement/download', 'SettlementController@download');

// Author:Jerry Date:180825 新增查询
Route::get('/settlement/platform', 'SettlementPlatformController@getList');
