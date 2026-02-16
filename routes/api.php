<?php

use App\Http\Controllers\Api\ActivoFijoApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventarioApiController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Authenticated (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Sync / Catalogs
    Route::get('/empresas', [SyncController::class, 'empresas']);
    Route::get('/empresas/{empresa}/sucursales', [SyncController::class, 'sucursales']);
    Route::get('/empresas/{empresa}/productos', [SyncController::class, 'productos']);
    Route::get('/empresas/{empresa}/lotes', [SyncController::class, 'lotesCaducidades']);
    Route::get('/statuses', [SyncController::class, 'statuses']);

    // Inventory (Product counting)
    Route::get('/inventarios', [InventarioApiController::class, 'index']);
    Route::post('/inventarios/upload', [InventarioApiController::class, 'upload']);

    // Fixed Assets
    Route::get('/activo-fijo', [ActivoFijoApiController::class, 'index']);
    Route::get('/activo-fijo-productos', [ActivoFijoApiController::class, 'productos']);
    Route::post('/activo-fijo/upload', [ActivoFijoApiController::class, 'upload']);
    Route::post('/activo-fijo/upload-imagen', [ActivoFijoApiController::class, 'uploadImagen']);
    Route::post('/activo-fijo/no-encontrados', [ActivoFijoApiController::class, 'uploadNoEncontrados']);
    Route::post('/activo-fijo/traspasos', [ActivoFijoApiController::class, 'uploadTraspasos']);
});
