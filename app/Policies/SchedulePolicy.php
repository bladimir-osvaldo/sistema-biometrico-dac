<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

/**
 * Política de autorización para la gestión de Horarios.
 *
 * - Administrador: gestión total.
 * - Coordinador DAC: solo horarios de los docentes que coordina.
 * - Docente: sin gestión (solo puede consultar su propio horario).
 */
class SchedulePolicy
{
    /**
     * Listar horarios: Admin y Coordinador (el Docente usa su ruta propia "Mi Horario").
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Coordinador DAC']);
    }

    /**
     * Ver un horario: Admin, Coordinador (si coordina al docente) y el propio docente.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole('Docente')) {
            return $schedule->user_id === $user->id;
        }

        if ($user->hasRole('Coordinador DAC')) {
            return $this->coordinaAlDocente($user, $schedule);
        }

        return $user->hasRole('Administrador');
    }

    /**
     * Crear horarios: Admin siempre; Coordinador solo si el docente seleccionado
     * pertenece a su coordinación (se recibe $docenteId como argumento adicional).
     */
    public function create(User $user, ?int $docenteId = null): bool
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasRole('Coordinador DAC')) {
            return $docenteId && $this->coordinaAlDocenteId($user, $docenteId);
        }

        return false;
    }

    /**
     * Actualizar horarios: Admin siempre; Coordinador si coordina al docente del horario.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }

        if ($user->hasRole('Coordinador DAC')) {
            return $this->coordinaAlDocente($user, $schedule);
        }

        return false;
    }

    /**
     * Eliminar horarios: misma regla que actualizar.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->update($user, $schedule);
    }

    /**
     * Determina si el coordinador coordina al docente asignado al horario.
     */
    protected function coordinaAlDocente(User $user, Schedule $schedule): bool
    {
        return $schedule->user_id && $this->coordinaAlDocenteId($user, $schedule->user_id);
    }

    /**
     * Determina si el coordinador coordina al docente con el id indicado.
     */
    protected function coordinaAlDocenteId(User $user, int $docenteId): bool
    {
        return User::where('id', $docenteId)
            ->where('coordinador_id', $user->id)
            ->exists();
    }
}
