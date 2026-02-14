<?php

use App\Http\Controllers\ActivoFijoController;
use App\Http\Controllers\ActivoFijoProductoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OrdenEntradaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Empresas
    Route::resource('empresas', EmpresaController::class)->except('show');

    // Sucursales
    Route::resource('sucursales', SucursalController::class)->except('show')->parameters(['sucursales' => 'sucursal']);

    // Usuarios
    Route::resource('usuarios', UsuarioController::class)->except('show');

    // Productos
    Route::get('/productos/importar', [ProductoController::class, 'importForm'])->name('productos.import.form');
    Route::post('/productos/importar', [ProductoController::class, 'import'])->name('productos.import');
    Route::resource('productos', ProductoController::class)->except('show');

    // Inventarios (AJAX helper for sucursales dropdown)
    Route::get('/sucursales-por-empresa/{empresa}', [InventarioController::class, 'sucursalesPorEmpresa']);
    Route::resource('inventarios', InventarioController::class);

    // Activo Fijo
    Route::get('/traspasos', [ActivoFijoController::class, 'traspasos'])->name('traspasos.index');
    Route::resource('activo-fijo', ActivoFijoController::class);

    // Catálogo de Activos Fijos
    Route::get('/activo-fijo-productos', [ActivoFijoProductoController::class, 'index'])->name('activo-fijo-productos.index');
    Route::get('/activo-fijo-productos/importar', [ActivoFijoProductoController::class, 'importForm'])->name('activo-fijo-productos.import.form');
    Route::post('/activo-fijo-productos/importar', [ActivoFijoProductoController::class, 'import'])->name('activo-fijo-productos.import');
    Route::get('/activo-fijo-productos/{producto}', [ActivoFijoProductoController::class, 'show'])->name('activo-fijo-productos.show');

    // Órdenes de Transferencia
    Route::get('/ordenes-entrada', [OrdenEntradaController::class, 'index'])->name('ordenes-entrada.index');
    Route::get('/ordenes-entrada/crear', [OrdenEntradaController::class, 'create'])->name('ordenes-entrada.create');
    Route::post('/ordenes-entrada', [OrdenEntradaController::class, 'store'])->name('ordenes-entrada.store');
    Route::get('/ordenes-entrada/{orden}', [OrdenEntradaController::class, 'show'])->name('ordenes-entrada.show');
    Route::post('/ordenes-entrada/{orden}/autorizar', [OrdenEntradaController::class, 'autorizar'])->name('ordenes-entrada.autorizar');
    Route::post('/ordenes-entrada/{orden}/surtir', [OrdenEntradaController::class, 'surtir'])->name('ordenes-entrada.surtir');
    Route::post('/ordenes-entrada/{orden}/rechazar', [OrdenEntradaController::class, 'rechazar'])->name('ordenes-entrada.rechazar');
    Route::post('/ordenes-entrada/{orden}/cancelar', [OrdenEntradaController::class, 'cancelar'])->name('ordenes-entrada.cancelar');

    // Reportes
    Route::get('/reportes/conteo', [ReporteController::class, 'conteo'])->name('reportes.conteo');
    Route::get('/reportes/no-encontrados', [ReporteController::class, 'noEncontrados'])->name('reportes.no-encontrados');
    Route::get('/reportes/global', [ReporteController::class, 'global'])->name('reportes.global');
    Route::get('/reportes/acumulado', [ReporteController::class, 'acumulado'])->name('reportes.acumulado');
    Route::get('/reportes/sesiones-movil', [ReporteController::class, 'sesionesMovil'])->name('reportes.sesiones-movil');

    // Transferencias
    Route::get('/transferencias/nueva', [TransferenciaController::class, 'nueva'])->name('transferencias.nueva');
    Route::post('/transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store');
    Route::get('/transferencias/solicitadas', [TransferenciaController::class, 'solicitadas'])->name('transferencias.solicitadas');
    Route::get('/transferencias/recibidas', [TransferenciaController::class, 'recibidas'])->name('transferencias.recibidas');
});
