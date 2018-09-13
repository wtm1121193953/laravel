<?php

use Illuminate\Support\Facades\Route;

Route::prefix('developer')
    ->namespace('Developer')
    ->middleware('developer')->group(function (){

        Route::post('login', 'SelfController@login');
        Route::post('logout', 'SelfController@logout');
        Route::get('self/rules', 'SelfController@getRules');
        Route::post('self/modifyPassword', 'SelfController@modifyPassword');

        Route::get('home/index', 'HomeController@index');
        Route::get('home/phpinfo', 'HomeController@phpinfo');

    });
