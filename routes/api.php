<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('test', function(){
    dump(Session::all());
});

Route::prefix('admin')
    ->middleware('admin')
    ->namespace('Admin')
    ->group(function(){
    Route::post('login', 'UserController@login');
    Route::get('user/rules', 'UserController@getRules');

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

});