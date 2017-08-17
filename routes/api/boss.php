<?php

use \Illuminate\Support\Facades\Route;

Route::get('test', 'LoginController@test');
Route::post('login', 'LoginController@login');