<?php

use Illuminate\Support\Facades\Route;

Route::prefix('setting')
    ->group(function (){
        Route::get('/payToPlatform', 'OperSettingController@getPayToPlatformStatus');
        Route::post('/setPayToPlatform', 'OperSettingController@setPayToPlatformStatus');
    });