<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderNotificationController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/', 'App\Http\Controllers\OrderNotificationController@receiveNotification');

