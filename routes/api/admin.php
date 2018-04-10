<?php
/**
 * 后台管理接口路由
 */
use Illuminate\Support\Facades\Route;


Route::prefix('admin')
    ->namespace('Admin')
    ->middleware('admin')->group(function (){

    Route::post('login', 'SelfController@login');
    Route::post('logout', 'SelfController@logout');
    Route::get('self/rules', 'SelfController@getRules');

    Route::get('users', 'UserController@getList');
    Route::post('user/add', 'UserController@add');
    Route::post('user/edit', 'UserController@edit');
    Route::post('user/del', 'UserController@del');
    Route::post('user/changeStatus', 'UserController@changeStatus');
    Route::post('user/resetPassword', 'UserController@resetPassword');

    Route::get('groups', 'GroupController@getList');
    Route::post('group/add', 'GroupController@add');
    Route::post('group/edit', 'GroupController@edit');
    Route::post('group/del', 'GroupController@del');
    Route::post('group/changeStatus', 'GroupController@changeStatus');

    Route::get('rules', 'RuleController@getList');
    Route::get('rules/tree', 'RuleController@getTree');
    Route::post('rule/add', 'RuleController@add');
    Route::post('rule/edit', 'RuleController@edit');
    Route::post('rule/del', 'RuleController@del');
    Route::post('rule/changeStatus', 'RuleController@changeStatus');

    Route::group([], base_path('routes/api/admin/goods.php'));
    Route::group([], base_path('routes/api/admin/oper.php'));
    Route::group([], base_path('routes/api/admin/oper_account.php'));

});
