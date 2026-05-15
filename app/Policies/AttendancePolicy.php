<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

/**
 * Política de autorización para el módulo de Asistencias.
 * Todos los roles autenticados pueden consultar; el alcance de los datos
 * se aplica en el controlador (Docente ve solo sus registros).
 */
class AttendancePolicy
{
    /**
     * Todos los roles autenticados pueden listar asistencias.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Coordinador DAC', 'Docente']);
    }

    /**
     * Permite exportar reportes (Admin y Coordinador exportan todos;
     * el Docente solo sus propios registros).
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Coordinador DAC', 'Docente']);
    }

    /**
     * Un docente solo puede consultar sus propias asistencias.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->hasRole('Docente')) {
            return $attendance->user_id === $user->id;
        }

        return $user->hasAnyRole(['Administrador', 'Coordinador DAC']);
    }
}
