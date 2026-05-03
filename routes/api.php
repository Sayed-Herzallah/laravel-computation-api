<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\AppSettingController;

/*
|--------------------------------------------------------------------------
| Math System — API Routes
|--------------------------------------------------------------------------
*/

// ======================== Auth ========================
Route::prefix('auth')->group(function () {
    Route::post('/login',  [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
});

// ======================== Protected ========================
Route::middleware('jwt.auth')->group(function () {

    // ---------- Trainer Only ----------
    Route::middleware('trainer')->group(function () {

        // إدارة الطلاب
        Route::get('/students',              [StudentController::class, 'index']);
        Route::post('/students',             [StudentController::class, 'store']);
        Route::get('/students/{id}',         [StudentController::class, 'show']);
        Route::get('/students/{id}/report',  [StudentController::class, 'report']);
        Route::put('/students/{id}/level',   [StudentController::class, 'updateLevel']);

        // إدارة التدريبات
        Route::post('/exercises/generate',         [ExerciseController::class, 'generate']);
        Route::post('/exercises/start',            [ExerciseController::class, 'startSession']);
        Route::post('/exercises/trainer-answer',   [ExerciseController::class, 'trainerSubmitAnswer']);

        // إدارة المسابقات
        Route::post('/competitions',               [CompetitionController::class, 'store']);
        Route::get('/competitions/{id}/results',   [CompetitionController::class, 'results']);

        // إعدادات التطبيق
        Route::get('/settings',  [AppSettingController::class, 'index']);
        Route::post('/settings', [AppSettingController::class, 'upsert']);
    });

    // ---------- Student ----------
    Route::post('/exercises/answer',           [ExerciseController::class, 'submitAnswer']);
    Route::get('/competitions',                [CompetitionController::class, 'index']);
    Route::post('/competitions/{id}/start',    [CompetitionController::class, 'startSession']);
    Route::post('/competitions/answer',        [CompetitionController::class, 'submitAnswer']);
});
