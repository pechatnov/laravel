<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Reviews API',
        'version' => '1.0.0',
        'description' => 'REST API для отзывов о компаниях и пользователях',
        'endpoints' => [
            'health' => '/health',
            'users' => '/api/users',
            'companies' => '/api/companies',
            'reviews' => '/api/reviews',
            'top-rated' => '/api/companies/top-rated',
        ],
        'documentation' => 'См. README.md для полной документации',
    ]);
});

Route::get('/health', fn () => response()->json(['ok' => true]));
