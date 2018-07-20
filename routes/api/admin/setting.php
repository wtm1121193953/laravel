<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/20
 * Time: 12:15
 */
use Illuminate\Support\Facades\Route;

Route::get('settings', 'SettingController@getList');
Route::post('setting/edit', 'SettingController@edit');
Route::get('setting/getCreditRulesList', 'SettingController@getCreditRulesList');
Route::post('setting/setCreditRules', 'SettingController@setCreditRules');
Route::post('setting/setArticle', 'SettingController@setArticle');
Route::get('setting/getArticle', 'SettingController@getArticle');

Route::get('setting/filterKeywords', 'FilterKeywordController@getList');
Route::post('setting/filterKeyword/add', 'FilterKeywordController@add');
Route::post('setting/filterKeyword/edit', 'FilterKeywordController@edit');
Route::post('setting/filterKeyword/changeStatus', 'FilterKeywordController@changeStatus');
Route::post('setting/filterKeyword/delete', 'FilterKeywordController@delete');
