<?php

use \Illuminate\Support\Facades\Route;

Route::get('test', 'LoginController@test');
Route::post('login', 'LoginController@login');

Route::get('users', 'UserController@getList');

Route::get('rules', 'RuleController@getList');
Route::get('rule/getTopList', 'RuleController@getTopList');