<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Justificación de Inasistencia / Tardanzas.
 */
class Justification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'fecha_inasistencia',
        'motivo',
        'descripcion',
        'archivo_adjunto',
        'estado',
        'revisado_por',
        'comentario_coordinador',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_inasistencia' => 'date',
        'fecha_revision' => 'datetime',
    ];

    /**
     * Docente que solicita la justificación.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Asistencia que se justifica.
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Coordinador que revisó la solicitud.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }
}
