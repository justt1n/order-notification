<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderNotificationController;

Route::post('/order', 'App\Http\Controllers\OrderNotificationController@receiveNotification');
Route::get('/order', function () {
    return 200;
});
