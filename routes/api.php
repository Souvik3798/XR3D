<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Model3DController;
use App\Http\Controllers\ModelFormatsController;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/models', [Model3DController::class, 'listAllModels']);
Route::get('/users', function () {
    return response()->json(['users' => \App\Models\User::all()]);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [UserController::class, 'logout']);

    // Model3D routes
    Route::post('/models', [Model3DController::class, 'create']);
    Route::put('/models/{id}', [Model3DController::class, 'update']);
    Route::delete('/models/{id}', [Model3DController::class, 'delete']);
    Route::get('/models/{id}/edit', [Model3DController::class, 'edit']);
    Route::get('/models/user', [Model3DController::class, 'listUserModels']);

    // ModelFormats routes
    Route::post('/models/{model3dId}/formats', [ModelFormatsController::class, 'create']);
    Route::put('/model-formats/{id}', [ModelFormatsController::class, 'update']);
    Route::delete('/model-formats/{id}', [ModelFormatsController::class, 'delete']);
    Route::get('/model-formats/{id}', [ModelFormatsController::class, 'show']);
    Route::get('/models/{model3dId}/formats', [ModelFormatsController::class, 'index']);
});
