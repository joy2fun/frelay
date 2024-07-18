<?php

use App\Http\Controllers\EndpointController;
use Illuminate\Support\Facades\Route;

Route::any('endpoint/{slug}', [EndpointController::class, 'relay'])->withoutMiddleware([]);
