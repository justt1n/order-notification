<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderNotificationController;

Route::get('/', function () {
    return view('welcome');
});
