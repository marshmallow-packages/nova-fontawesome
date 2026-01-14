<?php

use Illuminate\Support\Facades\Route;
use Marshmallow\NovaFontAwesome\Http\Controllers\FontAwesomeController;

Route::get('/search', [FontAwesomeController::class, 'search']);
Route::get('/icon/{name}', [FontAwesomeController::class, 'icon']);
Route::get('/popular', [FontAwesomeController::class, 'popular']);
Route::get('/metadata', [FontAwesomeController::class, 'metadata']);
Route::get('/debug', [FontAwesomeController::class, 'debug']);
Route::get('/fallback', [FontAwesomeController::class, 'fallback']);
