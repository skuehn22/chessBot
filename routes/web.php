<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardRecognitionController;

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
