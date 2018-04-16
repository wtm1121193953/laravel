<?php
use Illuminate\Support\Facades\Route;

Route::get('/settlements', 'SettlementController@getList');

Route::get('/getSettlementOrders', 'SettlementController@getSettlementOrders');

Route::post('/updateInvoice', 'SettlementController@updateInvoice');

Route::post('/updatePayPicUrl', 'SettlementController@updatePayPicUrl');