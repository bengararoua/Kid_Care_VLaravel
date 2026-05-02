<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');

Route::middleware('auth')->group(function () {
    Route::get('/export/child/{childId}', [ExportController::class, 'exportChildReport'])->name('export.child');
});