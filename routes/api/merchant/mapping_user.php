<?php
use Illuminate\Support\Facades\Route;

Route::get('/getMappingUser', 'MappingUserController@getMappingUser');
Route::get('/getUser', 'MappingUserController@getUser');
Route::post('/merchantBindUser', 'MappingUserController@merchantBindUser');