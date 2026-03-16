<?php

use App\Http\Controllers\FrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/articles/explore", [FrontController::class, "explore"]);
Route::get("/categories", [FrontController::class, "categories"]);
Route::get('/articles/ranking', [FrontController::class, 'ranking']);
Route::get('/articles/{id}', [FrontController::class, 'articleDetail']);
Route::get('/articles/{id}/comments', [FrontController::class, 'comments']);
