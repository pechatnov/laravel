<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('companies/top-rated', [CompanyController::class, 'topRated']);
Route::get('companies/{company}/statistics', [CompanyController::class, 'statistics']);
Route::get('companies/{company}/reviews', [ReviewController::class, 'forCompany']);
Route::get('users/{user}/reviews', [ReviewController::class, 'forUser']);

Route::apiResource('users', UserController::class);
Route::apiResource('companies', CompanyController::class);
Route::apiResource('reviews', ReviewController::class);
