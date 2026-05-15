<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuellaDocente extends Model
{
    use HasFactory;

    protected $table = 'huellas_docentes';

    protected $fillable = [
        'user_id',
        'dedo_numero',
        'dedo_nombre',
        'r307_id',
        'template_hash',
        'registrado_por',
        'estado',
    ];

    /**
     * Relación con el usuario (docente)
     */
    public function docente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el usuario que registró
     */
    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Scope para huellas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    /**
     * Número máximo de huellas por docente
     */
    public static function getMaxHuellas()
    {
        return 3;
    }
}
