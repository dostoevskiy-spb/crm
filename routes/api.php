<?php

use App\Http\Controllers\Api\IndividualController;
use App\Http\Controllers\Api\PingController;
use App\Infrastructure\Http\Controller\LegalEntityController;
use App\Infrastructure\Http\Controller\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', [PingController::class, 'get']);
Route::post('/ping', [PingController::class, 'post']);

// Individuals API routes
Route::get('/individuals', [IndividualController::class, 'index']);
Route::post('/individuals', [IndividualController::class, 'store']);
Route::get('/individuals/{uid}', [IndividualController::class, 'show'])->where('uid', '[0-9a-fA-F-]{36}');

// Legal Entities API routes
Route::get('/legal-entities', [LegalEntityController::class, 'index']);
Route::post('/legal-entities', [LegalEntityController::class, 'store']);
Route::get('/legal-entities/{uid}', [LegalEntityController::class, 'show'])->where('uid', '[0-9a-fA-F-]{36}');

// Products API routes
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{uid}', [ProductController::class, 'show'])->where('uid', '[0-9a-fA-F-]{36}');
