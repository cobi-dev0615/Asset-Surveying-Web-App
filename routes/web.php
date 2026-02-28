<?php

use App\Http\Controllers\ActivoFijoController;
use App\Http\Controllers\ActivoFijoProductoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EmpresaSelectionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OrdenEntradaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
    Route::post('/register', [LoginController::class, 'register']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // === Empresa selection (no empresa middleware — this IS the selection) ===
    Route::get('/seleccionar-empresa', [EmpresaSelectionController::class, 'show'])->name('seleccionar-empresa');
    Route::post('/seleccionar-empresa', [EmpresaSelectionController::class, 'store']);
    Route::get('/cambiar-empresa', [EmpresaSelectionController::class, 'cambiar'])->name('cambiar-empresa');
    Route::get('/sucursales-por-empresa/{empresa}', [InventarioController::class, 'sucursalesPorEmpresa']);

    // === All routes below require empresa selection ===
    Route::middleware('empresa')->group(function () {
        // === All authenticated web users ===
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/avance-general', [DashboardController::class, 'refreshAvanceGeneral']);
        Route::get('/dashboard/avance-area', [DashboardController::class, 'refreshAvanceArea']);
        Route::get('/dashboard/avance-categoria', [DashboardController::class, 'refreshAvanceCategoria']);
        Route::get('/dashboard/sesiones', [DashboardController::class, 'sesiones']);

        // === Super Admin only ===
        Route::middleware('role:super_admin')->group(function () {
            Route::resource('usuarios', UsuarioController::class)->except('show');
        });

        // === Super Admin + Supervisor ===
        Route::middleware('role:super_admin,supervisor')->group(function () {
            // Empresas & Sucursales (write ops protected in controller)
            Route::get('/empresas/exportar', [EmpresaController::class, 'exportar'])->name('empresas.export');
            Route::get('/empresas/{empresa}/imagenes/stats', [EmpresaController::class, 'imageStats'])->name('empresas.imagenes.stats');
            Route::post('/empresas/{empresa}/imagenes/reducir', [EmpresaController::class, 'reducirImagenes'])->name('empresas.imagenes.reducir');
            Route::post('/empresas/{empresa}/imagenes/redimensionar', [EmpresaController::class, 'redimensionarImagenes'])->name('empresas.imagenes.redimensionar');
            Route::post('/empresas/{empresa}/imagenes/eliminar', [EmpresaController::class, 'eliminarImagenes'])->name('empresas.imagenes.eliminar');
            Route::resource('empresas', EmpresaController::class)->except('show');
            Route::get('/sucursales/exportar', [SucursalController::class, 'exportar'])->name('sucursales.export');
            Route::post('/sucursales/{sucursal}/imagenes-residuales', [SucursalController::class, 'eliminarImagenesResiduales'])->name('sucursales.imagenes.residuales');
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
            Route::post('/activo-fijo-productos', [ActivoFijoProductoController::class, 'store'])->name('activo-fijo-productos.store');
            Route::get('/activo-fijo-productos/importar', [ActivoFijoProductoController::class, 'importForm'])->name('activo-fijo-productos.import.form');
            Route::post('/activo-fijo-productos/importar', [ActivoFijoProductoController::class, 'import'])->name('activo-fijo-productos.import');
            Route::get('/activo-fijo-productos/{producto}', [ActivoFijoProductoController::class, 'show'])->name('activo-fijo-productos.show');
            Route::get('/activo-fijo-productos/{producto}/editar', [ActivoFijoProductoController::class, 'edit'])->name('activo-fijo-productos.edit');
            Route::put('/activo-fijo-productos/{producto}', [ActivoFijoProductoController::class, 'update'])->name('activo-fijo-productos.update');
            Route::delete('/activo-fijo-productos/{producto}', [ActivoFijoProductoController::class, 'destroy'])->name('activo-fijo-productos.destroy');

            // Reportes
            Route::get('/reportes/conteo', [ReporteController::class, 'conteo'])->name('reportes.conteo');
            Route::get('/reportes/conteo/exportar', [ReporteController::class, 'exportConteo'])->name('reportes.conteo.export');
            Route::get('/reportes/conteo/imprimir', [ReporteController::class, 'conteoImprimir'])->name('reportes.conteo.print');
            Route::post('/reportes/conteo/eliminar', [ReporteController::class, 'conteoEliminar'])->name('reportes.conteo.delete');
            Route::match(['get', 'put'], '/reportes/conteo/{registro}/editar', [ReporteController::class, 'conteoEditar'])->name('reportes.conteo.edit');
            Route::get('/reportes/no-encontrados', [ReporteController::class, 'noEncontrados'])->name('reportes.no-encontrados');
            Route::get('/reportes/no-encontrados/exportar', [ReporteController::class, 'exportNoEncontrados'])->name('reportes.no-encontrados.export');
            Route::post('/reportes/no-encontrados/desmarcar', [ReporteController::class, 'noEncontradosDesmarcar'])->name('reportes.no-encontrados.unmark');
            Route::get('/reportes/global', [ReporteController::class, 'global'])->name('reportes.global');
            Route::get('/reportes/global/exportar', [ReporteController::class, 'exportGlobal'])->name('reportes.global.export');
            Route::get('/reportes/global/imprimir', [ReporteController::class, 'globalImprimir'])->name('reportes.global.print');
            Route::get('/reportes/acumulado', [ReporteController::class, 'acumulado'])->name('reportes.acumulado');
            Route::get('/reportes/acumulado/exportar', [ReporteController::class, 'exportAcumulado'])->name('reportes.acumulado.export');
            Route::get('/reportes/acumulado/imprimir', [ReporteController::class, 'acumuladoImprimir'])->name('reportes.acumulado.print');
            Route::get('/reportes/sesiones-movil', [ReporteController::class, 'sesionesMovil'])->name('reportes.sesiones-movil');
            Route::get('/reportes/sesiones-movil/exportar', [ReporteController::class, 'exportSesionesMovil'])->name('reportes.sesiones-movil.export');
        });

        // === Super Admin + Supervisor + Supervisor Invitado + Capturista ===
        Route::middleware('role:super_admin,supervisor,supervisor_invitado,capturista')->group(function () {
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
            Route::get('/transferencias/activos-por-sucursal', [TransferenciaController::class, 'activosPorSucursal'])->name('transferencias.activos');
            Route::post('/transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store');
            Route::get('/transferencias/solicitadas', [TransferenciaController::class, 'solicitadas'])->name('transferencias.solicitadas');
            Route::get('/transferencias/solicitadas/exportar', [TransferenciaController::class, 'exportSolicitadas'])->name('transferencias.solicitadas.export');
            Route::post('/transferencias/{traspaso}/autorizar', [TransferenciaController::class, 'autorizar'])->name('transferencias.autorizar');
            Route::post('/transferencias/{traspaso}/surtir', [TransferenciaController::class, 'surtir'])->name('transferencias.surtir');
            Route::post('/transferencias/{traspaso}/cancelar', [TransferenciaController::class, 'cancelar'])->name('transferencias.cancelar');
            Route::get('/transferencias/recibidas', [TransferenciaController::class, 'recibidas'])->name('transferencias.recibidas');
        });
    });
});
