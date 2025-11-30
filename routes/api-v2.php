<?php

use App\Http\Controllers\API\v2\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::post('submit', [ApiController::class, 'submission']);
    Route::get('tasks', [ApiController::class, 'tasks']);
    Route::post('image-submissions', [ApiController::class, 'submitImages']);
    Route::post('refresh-task-list', [ApiController::class, 'refreshTaskListing']);

    Route::get('categories', [ApiController::class, 'categories']);
    Route::get('products', [ApiController::class, 'products']);
    Route::get('uoms', [ApiController::class, 'uoms']);
});