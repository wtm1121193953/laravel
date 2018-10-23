<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/merchants', 'MerchantController@getList');
Route::get('/merchant/allNames', 'MerchantController@allNames');
Route::get('/merchant/detail', 'MerchantController@detail');
Route::get('/merchant/getMerchantById', 'MerchantController@detail');

Route::get('/merchant/audit/record/newest', 'MerchantController@getNewestAuditRecord');

Route::get('/merchant/isPayToPlatform', 'MerchantController@isPayToPlatform');

Route::post('/merchant/add', 'MerchantController@add');
Route::post('/merchant/edit', 'MerchantController@edit');
Route::post('/merchant/changeStatus', 'MerchantController@changeStatus');
Route::get('/merchant/export', 'MerchantController@export');
//Route::post('/merchant/del', 'MerchantController@del');

Route::post('/merchant/createAccount', 'MerchantController@createAccount');
Route::post('/merchant/editAccount', 'MerchantController@editAccount');

Route::post('/merchant/addFromMerchantPool', 'MerchantController@addFromMerchantPool');

Route::get('/merchant/audit/list', 'MerchantController@getAuditList');

Route::get('/merchant/pool', 'MerchantPoolController@getList');
Route::get('/merchant/pool/detail', 'MerchantPoolController@detail');
Route::post('/merchant/pool/add', 'MerchantPoolController@add');
Route::post('/merchant/pool/edit', 'MerchantPoolController@edit');
Route::post('/merchant/pool/del', 'MerchantPoolController@del');

Route::post('/merchant/draft/add', 'MerchantDraftController@add');
Route::get('/merchant/drafts', 'MerchantDraftController@getList');
Route::get('/merchant/draft/detail', 'MerchantDraftController@detail');
Route::post('/merchant/draft/edit', 'MerchantDraftController@edit');
Route::post('/merchant/draft/delete', 'MerchantDraftController@delete');