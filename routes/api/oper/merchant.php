<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/merchants', 'MerchantController@getList');
Route::get('/merchants/all', 'MerchantController@getAllList');
Route::get('/merchant/detail', 'MerchantController@detail');
Route::get('/merchant/getMerchantById', 'MerchantController@detail');
Route::post('/merchant/add', 'MerchantController@add');
Route::post('/merchant/edit', 'MerchantController@edit');
Route::post('/merchant/changeStatus', 'MerchantController@changeStatus');
//Route::post('/merchant/del', 'MerchantController@del');

Route::post('/merchant/createAccount', 'MerchantController@createAccount');
Route::post('/merchant/editAccount', 'MerchantController@editAccount');

Route::get('/merchant/addFromMerchantPool', 'MerchantController@addFromMerchantPool');

Route::get('/merchant/pool', 'MerchantPoolController@getList');
Route::get('/merchant/pool/detail', 'MerchantPoolController@detail');
Route::get('/merchant/pool/add', 'MerchantPoolController@add');
Route::get('/merchant/pool/edit', 'MerchantPoolController@edit');