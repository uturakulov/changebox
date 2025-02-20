<?php

use App\Http\Controllers\BatchController;
use Illuminate\Support\Facades\Route;

Route::post('/batches', [BatchController::class, 'store']);
