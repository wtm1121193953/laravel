<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/operBizMembers', 'OperBizMemberController@getList');
Route::get('/operBizMembers/search', 'OperBizMemberController@search');
Route::get('/operBizMembers/detail', 'OperBizMemberController@detail');
Route::post('/operBizMember/add', 'OperBizMemberController@add');
Route::post('/operBizMember/edit', 'OperBizMemberController@edit');
Route::post('/operBizMember/changeStatus', 'OperBizMemberController@changeStatus');
Route::post('/operBizMember/del', 'OperBizMemberController@del');

Route::get('/operBizMember/merchants', 'OperBizMemberController@getMerchants');
Route::get('/bizerRecord', 'BizerRecordController@getList');
Route::get('/bizerRecord/contractBizer', 'BizerRecordController@contractBizer');

Route::get('/oper/bizers', 'MyBizerController@getList');
Route::get('/operBizer/changeDetail', 'MyBizerController@changeDetail');
Route::get('/operBizer/changeStatus', 'MyBizerController@changeOperBizerSignStatus');
Route::get('/operBizer/merchants', 'MyBizerController@getMerchants');

Route::get('/operBizer/getbizers', 'OperBizMemberController@getAllbizer');


