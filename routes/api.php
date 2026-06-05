<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/auth/login", [AuthController::class, "login"]);
Route::post("/auth/send-code", [AuthController::class, "sendEmailCode"]);
Route::post("/auth/email-login", [AuthController::class, "emailLogin"]);
Route::post("/auth/logout", [AuthController::class, "logout"]);
Route::post("/auth/register", [AuthController::class, "register"]);
Route::post("/auth/refresh",[AuthController::class,"refresh"]);

Route::get("/articles/explore", [FrontController::class, "explore"]);
Route::get("/categories", [FrontController::class, "categories"]);
Route::get("/articles/ranking", [FrontController::class, "ranking"]);
Route::get("/articles/{id}", [FrontController::class, "articleDetail"]);
Route::get("/articles/{id}/comments", [FrontController::class, "comments"]);
