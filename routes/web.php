<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardRecognitionController;
use App\Http\Controllers\MapillaryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('app');
});

Route::get('/test', function () {
    return '<h1>Laravel is working!</h1>';
});

// Board recognition API routes
Route::post('/api/recognize-board', [BoardRecognitionController::class, 'recognizeBoard']);
Route::post('/api/analyze-position', [BoardRecognitionController::class, 'analyzePosition']);

// Mapillary Test Routes - Completely separate from main app
Route::get('/mapillary-test', [MapillaryController::class, 'index'])->name('mapillary.test');
Route::get('/api/mapillary/search-berlin', [MapillaryController::class, 'searchBerlinImages']);
Route::get('/api/mapillary/area-images', [MapillaryController::class, 'getAreaImages']);
Route::get('/api/mapillary/construction-hotspots', [MapillaryController::class, 'getConstructionHotspots']);
