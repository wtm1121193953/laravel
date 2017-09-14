<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/boss', function () {
    return view('boss');
});

Route::get('/react', function () {
    return view('react');
});

Route::get('/react-antd', function(){
    return view('react-antd');
});


Route::get('/home', 'HomeController@index')->name('home');
