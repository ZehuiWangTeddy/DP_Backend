<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('login', function(){
    return response()->json([
        'meta' => [
            'code' => 401,
            'message' => 'Unauthenticated.',
        ],
        'data' => [],
    ]);
})->name('login');

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('password/email', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:60,1');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->middleware(['auth:api']);
});
