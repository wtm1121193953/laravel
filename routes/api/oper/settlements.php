<?php
use Illuminate\Support\Facades\Route;

Route::get('/settlements', 'SettlementController@getList');
Route::get('/settlement/orders', 'SettlementController@getSettlementOrders');
Route::get('/settlements/export', 'SettlementController@export');
Route::post('/settlements/updateInvoice', 'SettlementController@updateInvoice');
Route::post('/settlements/updatePayPicUrl', 'SettlementController@updatePayPicUrl');