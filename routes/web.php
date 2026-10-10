<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Everything public is resolved from the database by canonical_path.
Route::get('/', [PageController::class, 'show']);
Route::fallback([PageController::class, 'show']);
