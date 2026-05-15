<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\JustificationController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\BiometriaController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\MemorandoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardApiController;

/*
|--------------------------------------------------------------------------
| Web Routes – Sistema Biométrico DAC
|--------------------------------------------------------------------------
*/

// Redirección inicial
Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticación Web
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================
// RUTAS PROTEGIDAS POR AUTENTICACIÓN
// =====================================================
Route::middleware(['auth'])->group(function () {

    // Dashboard – visible para todos los autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ──────────────────────────────────────────────────────────
    // PERFIL PROPIO (disponible para todos los roles)
    // ──────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ──────────────────────────────────────────────────────────
    // API AJAX (Dashboard: búsqueda de docentes y reportes JSON)
    // ──────────────────────────────────────────────────────────
    Route::get('/api/search/docentes', [DashboardApiController::class, 'searchDocentes'])->name('api.search.docentes');
    Route::get('/api/reports/attendance', [DashboardApiController::class, 'attendanceReport'])->name('api.reports.attendance');

    // --------------------------------------------------------------
    // 1. RUTAS EXCLUSIVAS PARA ADMIN
    // --------------------------------------------------------------
    Route::middleware(['role:admin'])->group(function () {
        // Gestión de Usuarios y Docentes
        Route::resource('users', UserController::class);

        // Gestión de Dispositivos ESP32
        Route::resource('devices', DeviceController::class);

        // Gestión Biométrica (Enrolamiento)
        Route::get('/biometria', [BiometriaController::class, 'index'])->name('biometria.index');
        Route::post('/biometria', [BiometriaController::class, 'store'])->name('biometria.store');

        // Centro de Monitoreo de Hardware
        Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');
        Route::get('/monitoreo/estado', [MonitoreoController::class, 'estadoDispositivos'])->name('monitoreo.estado');
    });

    // --------------------------------------------------------------
    // 2. RUTAS PARA ADMIN Y COORDINADOR
    // --------------------------------------------------------------
    Route::middleware(['role:admin,coordinador'])->group(function () {
        // Gestión de Horarios
        Route::resource('schedules', ScheduleController::class);

        // Justificaciones – revisar y aprobar
        Route::put('/justifications/{id}/review', [JustificationController::class, 'review'])->name('justifications.review');

        // Memorandos Automáticos
        Route::get('/memorandos', [MemorandoController::class, 'index'])->name('memorandos.index');
        Route::post('/memorandos/generar', [MemorandoController::class, 'generar'])->name('memorandos.generar');

        // Reportes Avanzados PDF/Excel
        Route::get('/reports/mensual/{docente_id?}/{mes?}/{anio?}', [ReporteController::class, 'reporteMensual'])->name('reports.mensual');
        Route::get('/reports/tardanzas/{docente_id?}/{mes?}/{anio?}', [ReporteController::class, 'reporteTardanzas'])->name('reports.tardanzas');
        Route::get('/reports/excel/{docente_id?}/{mes?}/{anio?}', [ReporteController::class, 'exportarExcel'])->name('reports.excel');
    });

    // --------------------------------------------------------------
    // 3. RUTAS PARA ADMIN, COORDINADOR Y DOCENTE (todos)
    // --------------------------------------------------------------
    Route::middleware(['role:admin,coordinador,docente'])->group(function () {
        // Mi Horario (propio, disponible para todos los roles)
        Route::get('/mi-horario', [ScheduleController::class, 'misHorarios'])->name('schedule.mine');

        // Asistencias y Reportes
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('/attendances/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.export.pdf');
        Route::get('/attendances/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendances.export.excel');

        // Justificaciones – solicitar
        Route::get('/justifications', [JustificationController::class, 'index'])->name('justifications.index');
        Route::post('/justifications', [JustificationController::class, 'store'])->name('justifications.store');
    });

});