<?php
use Illuminate\Support\Facades\Route;

Route::get('/mappingUser/userInfo', 'MappingUserController@getMappingUser');
Route::post('/mappingUser/bindUser', 'MappingUserController@operBindUser');