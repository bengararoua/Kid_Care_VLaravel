<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

/*
|--------------------------------------------------------------------------
| Routes protégées (auth requise)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/export/child/{childId}', [ExportController::class, 'exportChildReport'])
        ->name('export.child');
});

/*
|--------------------------------------------------------------------------
| Catch-all → SPA Vue/React (DOIT être en dernier)
|--------------------------------------------------------------------------
*/
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');