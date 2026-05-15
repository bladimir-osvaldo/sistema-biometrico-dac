<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\BiometriaApiController;
use App\Http\Controllers\BiometriaController;

/*
|--------------------------------------------------------------------------
| API Routes v1 – Sistema Biométrico DAC (IoT ESP32)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Registro de asistencias biométricas (tiempo real y lote offline)
    Route::post('/attendance', [AttendanceApiController::class, 'register']);

    // Configuración y sincronización del dispositivo ESP32
    Route::get('/device/config', [DeviceApiController::class, 'getConfig']);
    Route::post('/device/heartbeat', [DeviceApiController::class, 'heartbeat']);
    Route::get('/device/firmware/latest', [DeviceApiController::class, 'latestFirmware']);

    // Enrolamiento biométrico desde el hardware (sensor R307)
    Route::post('/biometria/enroll', [BiometriaApiController::class, 'enroll']);
    Route::post('/biometria/delete', [BiometriaApiController::class, 'delete']);
    Route::get('/biometria/list', [BiometriaApiController::class, 'list']);

    // Endpoint de verificación biométrica para hardware/web
    Route::match(['get', 'post'], '/biometria/verificar/{user_id}', [BiometriaController::class, 'verificar']);
});
