<?php
use Illuminate\Support\Facades\Route;

Route::get('/settlements', 'SettlementController@getList');