<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Asistencia Biométrica.
 */
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'device_id',
        'fecha',
        'hora_marcado',
        'estado',
        'modo_marcado',
        'minutos_retraso',
        'ip_origen',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Relación con el docente que marcó asistencia.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el horario programado.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Relación con el dispositivo IoT ESP32 que capturó el marcado.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Relación con justificación si existe.
     */
    public function justification()
    {
        return $this->hasOne(Justification::class);
    }
}
