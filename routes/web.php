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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'IndexController@index');

// login logout Plurk
Route::get('/login_plurk', 'IndexController@login_plurk')->name('login_plurk');
Route::get('/callback_plurk', 'IndexController@callback_plurk');
Route::get('/logout_plurk', 'IndexController@logout_plurk')->name('logout_plurk');

// Plurk API
Route::get('/testPlurkUpload', 'IndexController@testPlurkUpload');
Route::post('/uploadPlurkImage', 'IndexController@uploadPlurkImage');


// login logout Twitter
Route::get('/login_twitter', 'IndexController@login_twitter')->name('login_twitter');
Route::get('/callback_twitter', 'IndexController@callback_twitter');
Route::get('/logout_twitter', 'IndexController@logout_twitter')->name('logout_twitter');
