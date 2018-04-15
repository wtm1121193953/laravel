<?php
/**
 * 商家 订单管理 路由模块
 */

use Illuminate\Support\Facades\Route;

Route::get('/orders', 'OrdersController@getList');