<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Dispositivo Biométrico IoT (ESP32).
 */
class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion_aula',
        'mac_address',
        'firmware_version',
        'estado',
        'ultimo_heartbeat',
        'api_token',
    ];

    protected $casts = [
        'ultimo_heartbeat' => 'datetime',
    ];

    /**
     * Relación con asistencias registradas desde este dispositivo.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
