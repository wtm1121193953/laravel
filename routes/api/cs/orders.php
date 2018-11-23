<?php
/**
 * 商家 订单管理 路由模块
 */

use Illuminate\Support\Facades\Route;

Route::get('/orders', 'OrdersController@getList');

Route::post('/verification', 'OrdersController@verification');

Route::get('/orders/export', 'OrdersController@export');

Route::get('/orders/field/sta', 'OrdersController@getOrderFieldStatistics');

Route::post('/order/deliver', 'OrdersController@orderDeliver');

Route::post('/order/check/deliver_code', 'OrdersController@checkDeliverCode');

Route::get('/order/batch_delivery/template', 'OrdersController@getBatchDeliveryTemplatePath');

Route::post('/order/batch/delivery', 'OrdersController@batchDelivery');

