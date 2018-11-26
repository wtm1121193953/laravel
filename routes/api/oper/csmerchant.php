<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/cs/merchants', 'CsMerchantController@getList');
Route::get('/cs/merchant/allNames', 'CsMerchantController@allNames');
Route::get('/cs/merchant/detail', 'CsMerchantController@detail');
Route::get('/cs/merchant/getMerchantById', 'CsMerchantController@detail');

Route::get('/cs/merchant/audit/record/newest', 'CsMerchantController@getNewestAuditRecord');

Route::get('/cs/merchant/isPayToPlatform', 'CsMerchantController@isPayToPlatform');

Route::post('/cs/merchant/add', 'CsMerchantAuditController@editOrAdd');
Route::post('/cs/merchant/edit', 'CsMerchantAuditController@editOrAdd');
Route::post('/cs/merchant/changeStatus', 'CsMerchantController@changeStatus');
Route::get('/cs/merchant/export', 'CsMerchantController@export');
Route::post('/cs/merchant/recall', 'CsMerchantController@recall');

Route::post('/cs/merchant/createAccount', 'CsMerchantController@createAccount');
Route::post('/cs/merchant/editAccount', 'CsMerchantController@editAccount');

Route::post('/cs/merchant/addFromMerchantPool', 'CsMerchantController@addFromMerchantPool');

Route::get('/cs/merchant/audit/list', 'CsMerchantController@getAuditList');
Route::get('/cs/merchant/audit/detail', 'CsMerchantController@getAuditDetail');


Route::post('/merchant/draft/add', 'MerchantDraftController@add');
Route::get('/merchant/drafts', 'MerchantDraftController@getList');
Route::get('/merchant/draft/detail', 'MerchantDraftController@detail');
Route::post('/merchant/draft/edit', 'MerchantDraftController@edit');
Route::post('/merchant/draft/delete', 'MerchantDraftController@delete');