<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo de Usuario del Sistema Biométrico DAC.
 * Soporta autenticación web, tokens Sanctum para API y gestión de roles/permisos Spatie.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Campos asignables en masa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'dni',
        'codigo_docente',
        'telefono',
        'carrera',
        'cargo',
        'foto_perfil',
        'huella_template_hash',
        'estado',
        'coordinador_id',
    ];

    /**
     * Campos ocultos en las respuestas JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'huella_template_hash',
    ];

    /**
     * Conversión de tipos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relación: Horarios asignados al docente.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Relación: Registros de asistencia del docente.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relación: Solicitudes de justificación realizadas.
     */
    public function justifications()
    {
        return $this->hasMany(Justification::class);
    }

    /**
     * Relación: Huellas dactilares registradas del docente (hasta 3 dedos).
     */
    public function huellas()
    {
        return $this->hasMany(HuellaDocente::class);
    }

    /**
     * Relación hasOne con HuellaDocente.
     */
    public function huellaDocente()
    {
        return $this->hasOne(HuellaDocente::class);
    }

    /**
     * Relación: Coordinador DAC responsable de este docente.
     */
    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    /**
     * Relación: Docentes coordinados por este Coordinador DAC.
     */
    public function coordinados()
    {
        return $this->hasMany(User::class, 'coordinador_id');
    }

    /**
     * Accesor para obtener la URL de la foto de perfil o avatar por defecto.
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto_perfil) {
            return asset('storage/' . $this->foto_perfil);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6b0c24&color=ffffff&bold=true';
    }
}
