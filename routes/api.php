<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Documents CRUD
    Route::get('/documents/{id}/view', [DocumentController::class, 'viewFile']);
    Route::apiResource('documents', DocumentController::class);

    // Chat / Q&A
    Route::post('/chat', [\App\Http\Controllers\Api\ChatController::class, 'ask']);
});

// TEMPORARY: Database cleanup route for testing phase
// Access via: /api/nuke-database-secure-123?confirm=1
Route::get('/nuke-database-secure-123', function () {
    if (app()->environment('production') && !request()->has('confirm')) {
        return "Tambahkan ?confirm=1 di URL untuk menghapus.";
    }

    \Illuminate\Support\Facades\DB::statement('SET session_replication_role = "replica";');
    
    $tables = [
        'document_chunks', 'documents', 'personal_access_tokens', 
        'sessions', 'password_reset_tokens', 'users', 
        'cache', 'jobs', 'job_batches', 'failed_jobs', 'migrations'
    ];

    foreach ($tables as $table) {
        \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS \"$table\" CASCADE;");
    }

    \Illuminate\Support\Facades\DB::statement('SET session_replication_role = "origin";');

    return "Database Cleaned! Silakan jalankan php artisan migrate --force sekarang.";
});

