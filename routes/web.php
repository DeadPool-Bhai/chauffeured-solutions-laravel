<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::post('/lead/quote', [LeadController::class, 'quote'])->middleware('throttle:6,1')->name('lead.quote');

// Everything public is resolved from the database by canonical_path.
Route::get('/', [PageController::class, 'show']);
Route::fallback([PageController::class, 'show']);
