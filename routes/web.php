<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});

// Route::post('/api/auth/register', [AuthController::class, 'register']);
// Route::post('/api/auth/login', [AuthController::class, 'login']);

// // Rute CRUD yang dilindungi (Middleware harus dipanggil manual jika di web.php)
// Route::group(['middleware' => 'auth:sanctum'], function () {
//     // API Resource untuk Jadwal
//     Route::apiResource('api/schedules', ScheduleController::class);
// });