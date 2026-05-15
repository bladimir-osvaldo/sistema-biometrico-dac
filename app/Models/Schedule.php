<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Horarios de Clases.
 */
class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'materia',
        'codigo_materia',
        'aula',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'tolerancia_minutos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación con el docente asignado.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con asistencias marcadas para este horario.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
