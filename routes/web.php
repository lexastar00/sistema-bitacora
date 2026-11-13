<?php

use App\Http\Controllers\AtencionController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('can:bitacora.ver')->prefix('atenciones')->name('atenciones.')->group(function () {
        Route::get('/', [AtencionController::class, 'index'])->name('index');
        Route::get('/crear', [AtencionController::class, 'create'])->name('create')->middleware('can:bitacora.crear');
        Route::post('/', [AtencionController::class, 'store'])->name('store')->middleware('can:bitacora.crear');
        Route::get('/{atencion}', [AtencionController::class, 'show'])->whereNumber('atencion')->name('show');
        Route::put('/{atencion}', [AtencionController::class, 'update'])->whereNumber('atencion')->name('update');
        Route::post('/{atencion}/evidencias', [AtencionController::class, 'upload'])->whereNumber('atencion')->name('evidencias.upload');
        Route::get('/{atencion}/evidencias/{adjunto}', [AtencionController::class, 'download'])->whereNumber('atencion')->whereNumber('adjunto')->name('evidencias.download');
    });

    Route::middleware('can:catalogos.administrar')->prefix('personal')->name('personal.')->group(function () {
        Route::get('/', [PersonalController::class, 'index'])->name('index');
        Route::post('/', [PersonalController::class, 'store'])->name('store');
        Route::post('/{personal}/roles', [PersonalController::class, 'roles'])->whereNumber('personal')->name('roles');
    });

    Route::middleware('can:estudiantes.ver')->prefix('estudiantes')->name('estudiantes.')->group(function () {
        Route::get('/', [EstudianteController::class, 'index'])->name('index');
        Route::get('/crear', [EstudianteController::class, 'create'])->name('create')->middleware('can:estudiantes.crear');
        Route::post('/', [EstudianteController::class, 'store'])->name('store')->middleware('can:estudiantes.crear');
        Route::get('/{estudiante}', [EstudianteController::class, 'show'])->whereNumber('estudiante')->name('show');
        Route::put('/{estudiante}', [EstudianteController::class, 'update'])->whereNumber('estudiante')->name('update')->middleware('can:estudiantes.editar');
        Route::post('/{estudiante}/residencias', [TramiteController::class, 'residencia'])->whereNumber('estudiante')->name('residencias.store')->middleware('can:estudiantes.editar');
        Route::post('/{estudiante}/servicio-social', [TramiteController::class, 'servicioSocial'])->whereNumber('estudiante')->name('servicio-social.store')->middleware('can:estudiantes.editar');
        Route::post('/{estudiante}/actividades', [TramiteController::class, 'actividad'])->whereNumber('estudiante')->name('actividades.store')->middleware('can:estudiantes.editar');
        Route::put('/{estudiante}/residencias/{tramite}', [TramiteController::class, 'actualizarResidencia'])->whereNumber('estudiante')->whereNumber('tramite')->name('residencias.update')->middleware('can:estudiantes.editar');
        Route::put('/{estudiante}/servicio-social/{tramite}', [TramiteController::class, 'actualizarServicioSocial'])->whereNumber('estudiante')->whereNumber('tramite')->name('servicio-social.update')->middleware('can:estudiantes.editar');
    });

    Route::get('/seguimiento', SeguimientoController::class)->middleware(['can:seguimiento.ver'])->name('seguimiento.index');
    Route::get('/auditoria', AuditoriaController::class)->middleware(['can:auditoria.ver'])->name('auditoria.index');

    Route::middleware('can:documentos.ver_historial')->prefix('documentos')->name('documentos.')->group(function () {
        Route::get('/', [DocumentoController::class, 'index'])->name('index');
        Route::get('/crear', [DocumentoController::class, 'create'])->name('create')->middleware('can:documentos.generar');
        Route::post('/', [DocumentoController::class, 'store'])->name('store')->middleware('can:documentos.generar');
        Route::get('/{documento}/descargar', [DocumentoController::class, 'download'])->whereNumber('documento')->name('download')->middleware('can:documentos.descargar');
    });

    Route::middleware('can:usuarios.administrar')->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::put('/{usuario}', [UsuarioController::class, 'update'])->whereNumber('usuario')->name('update');
    });

    Route::middleware('can:catalogos.administrar')->prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/', [CatalogoController::class, 'index'])->name('index');
        Route::post('/{catalogo}', [CatalogoController::class, 'store'])->whereIn('catalogo', ['carreras', 'departamentos', 'servicios', 'tipos_atencion', 'tipos_problema', 'periodos_escolares'])->name('store');
        Route::patch('/{catalogo}/{id}/estado', [CatalogoController::class, 'toggle'])->whereIn('catalogo', ['carreras', 'departamentos', 'servicios', 'tipos_atencion', 'tipos_problema', 'periodos_escolares'])->whereNumber('id')->name('toggle');
    });
});
