<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\FileController;


Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('notes', NoteController::class);
    Route::apiResource('reminders', ReminderController::class);
    
    
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/reminders/{id}/files', [ReminderController::class, 'getFiles'])->name('reminders.files');
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::delete('/files/delete', [FileController::class, 'delete']);

});