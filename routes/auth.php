<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::post('register', 'PassportAuthController@register');

Route::post('login', 'PassportAuthController@login');

Route::middleware('auth:api')->get('me', 'PassportAuthController@me');
