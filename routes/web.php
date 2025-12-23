<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});