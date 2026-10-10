<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::post('/lead/quote', [LeadController::class, 'quote'])->middleware('throttle:6,1')->name('lead.quote');

Route::get('/sitemap_index.xml', [SitemapController::class, 'index']);
Route::get('/{kind}-sitemap.xml', [SitemapController::class, 'show'])->where('kind', 'page|post');
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// Everything public is resolved from the database by canonical_path.
Route::get('/', [PageController::class, 'show']);
Route::fallback([PageController::class, 'show']);
