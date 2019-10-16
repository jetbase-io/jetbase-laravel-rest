<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// auth
Route::post('/login', 'Auth\LoginController')->middleware('guest');
Route::delete('/logout', 'Auth\LogoutController')->middleware('auth');

// users
Route::post('/users', 'UsersController@create')->middleware('auth');
Route::get('/users', 'UsersController@search')->middleware('auth');
Route::get('/users/current', 'UsersController@current')->middleware('auth');
Route::get('/users/{user_id}', 'UsersController@getById')->middleware('auth')->where('user_id', '\d+');
Route::put('/users/{user_id}', 'UsersController@update')->middleware('auth')->where('user_id', '\d+');
Route::delete('/users/{user_id}', 'UsersController@deleteUser')->middleware('auth')->where('user_id', '\d+');
Route::put('/users/{user_id}/password', 'UsersController@changePassword')->middleware('auth')->where('user_id', '\d+');