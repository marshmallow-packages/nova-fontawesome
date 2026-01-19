<?php

use Illuminate\Support\Facades\Route;
use Marshmallow\NovaFontAwesome\Http\Controllers\FontAwesomeController;

Route::get('/search', [FontAwesomeController::class, 'search']);
Route::get('/icon/{name}', [FontAwesomeController::class, 'icon']);
Route::get('/metadata', [FontAwesomeController::class, 'metadata']);
Route::get('/config', [FontAwesomeController::class, 'config']);
Route::get('/convert', [FontAwesomeController::class, 'convert']);
Route::get('/debug', [FontAwesomeController::class, 'debug']);
Route::get('/fallback', [FontAwesomeController::class, 'fallback']);
