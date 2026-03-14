<?php

use App\Http\Controllers\FrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/articles/explore", [FrontController::class, "explore"]);
Route::get("/categories", [FrontController::class, "categories"]);
Route::get('/articles/ranking', [FrontController::class, 'ranking']);
