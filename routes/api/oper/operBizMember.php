<?php
/**
 * supplier 模块路由
 */
use Illuminate\Support\Facades\Route;

Route::get('/operBizMembers', 'OperBizMemberController@getList');
Route::get('/operBizMembers/search', 'OperBizMemberController@search');
Route::post('/operBizMember/add', 'OperBizMemberController@add');
Route::post('/operBizMember/edit', 'OperBizMemberController@edit');
Route::post('/operBizMember/changeStatus', 'OperBizMemberController@changeStatus');
Route::post('/operBizMember/del', 'OperBizMemberController@del');