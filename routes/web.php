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
Route::get('/login_plurk', 'IndexController@login_plurk');
Route::get('/callback_plurk', 'IndexController@callback_plurk');
Route::post('/uploadImage', 'IndexController@uploadImage');

Route::get('/login_twitter', 'IndexController@login_twitter');
Route::get('/callback_twitter', 'IndexController@callback_twitter');