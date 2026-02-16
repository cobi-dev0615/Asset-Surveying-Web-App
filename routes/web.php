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
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register']);

Route::middleware('auth')->group(function () {
    // === All authenticated web users ===
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sucursales-por-empresa/{empresa}', [InventarioController::class, 'sucursalesPorEmpresa']);

    // === Super Admin only ===
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except('show');
    });

    // === Super Admin + Supervisor ===
    Route::middleware('role:super_admin,supervisor')->group(function () {
        // Empresas & Sucursales (write ops protected in controller)
        Route::resource('empresas', EmpresaController::class)->except('show');
        Route::resource('sucursales', SucursalController::class)->except('show')->parameters(['sucursales' => 'sucursal']);

        // Productos
        Route::get('/productos/importar', [ProductoController::class, 'importForm'])->name('productos.import.form');
        Route::post('/productos/importar', [ProductoController::class, 'import'])->name('productos.import');
        Route::resource('productos', ProductoController::class)->except('show');

        // Inventarios
        Route::resource('inventarios', InventarioController::class);

        // Activo Fijo
        Route::get('/traspasos', [ActivoFijoController::class, 'traspasos'])->name('traspasos.index');
        Route::resource('activo-fijo', ActivoFijoController::class);

        // Catálogo de Activos Fijos
        Route::get('/activo-fijo-productos', [ActivoFijoProductoController::class, 'index'])->name('activo-fijo-productos.index');
        Route::get('/activo-fijo-productos/importar', [ActivoFijoProductoController::class, 'importForm'])->name('activo-fijo-productos.import.form');
        Route::post('/activo-fijo-productos/importar', [ActivoFijoProductoController::class, 'import'])->name('activo-fijo-productos.import');
        Route::get('/activo-fijo-productos/{producto}', [ActivoFijoProductoController::class, 'show'])->name('activo-fijo-productos.show');

        // Reportes
        Route::get('/reportes/conteo', [ReporteController::class, 'conteo'])->name('reportes.conteo');
        Route::get('/reportes/conteo/exportar', [ReporteController::class, 'exportConteo'])->name('reportes.conteo.export');
        Route::get('/reportes/no-encontrados', [ReporteController::class, 'noEncontrados'])->name('reportes.no-encontrados');
        Route::get('/reportes/no-encontrados/exportar', [ReporteController::class, 'exportNoEncontrados'])->name('reportes.no-encontrados.export');
        Route::get('/reportes/global', [ReporteController::class, 'global'])->name('reportes.global');
        Route::get('/reportes/global/exportar', [ReporteController::class, 'exportGlobal'])->name('reportes.global.export');
        Route::get('/reportes/acumulado', [ReporteController::class, 'acumulado'])->name('reportes.acumulado');
        Route::get('/reportes/acumulado/exportar', [ReporteController::class, 'exportAcumulado'])->name('reportes.acumulado.export');
        Route::get('/reportes/sesiones-movil', [ReporteController::class, 'sesionesMovil'])->name('reportes.sesiones-movil');
        Route::get('/reportes/sesiones-movil/exportar', [ReporteController::class, 'exportSesionesMovil'])->name('reportes.sesiones-movil.export');
    });

    // === Super Admin + Supervisor + Supervisor Invitado ===
    Route::middleware('role:super_admin,supervisor,supervisor_invitado')->group(function () {
        // Órdenes de Transferencia
        Route::get('/ordenes-entrada', [OrdenEntradaController::class, 'index'])->name('ordenes-entrada.index');
        Route::get('/ordenes-entrada/crear', [OrdenEntradaController::class, 'create'])->name('ordenes-entrada.create');
        Route::post('/ordenes-entrada', [OrdenEntradaController::class, 'store'])->name('ordenes-entrada.store');
        Route::get('/ordenes-entrada/{orden}', [OrdenEntradaController::class, 'show'])->name('ordenes-entrada.show');
        Route::post('/ordenes-entrada/{orden}/autorizar', [OrdenEntradaController::class, 'autorizar'])->name('ordenes-entrada.autorizar');
        Route::post('/ordenes-entrada/{orden}/surtir', [OrdenEntradaController::class, 'surtir'])->name('ordenes-entrada.surtir');
        Route::post('/ordenes-entrada/{orden}/rechazar', [OrdenEntradaController::class, 'rechazar'])->name('ordenes-entrada.rechazar');
        Route::post('/ordenes-entrada/{orden}/cancelar', [OrdenEntradaController::class, 'cancelar'])->name('ordenes-entrada.cancelar');

        // Transferencias
        Route::get('/transferencias/nueva', [TransferenciaController::class, 'nueva'])->name('transferencias.nueva');
        Route::post('/transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store');
        Route::get('/transferencias/solicitadas', [TransferenciaController::class, 'solicitadas'])->name('transferencias.solicitadas');
        Route::get('/transferencias/recibidas', [TransferenciaController::class, 'recibidas'])->name('transferencias.recibidas');
    });
});
