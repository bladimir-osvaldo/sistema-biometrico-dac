<?php

namespace App\Policies;

use App\Models\User;

/**
 * Política de autorización para la gestión de usuarios/docentes.
 * Solo el Administrador puede gestionar usuarios.
 */
class UserPolicy
{
    /**
     * Determina si el usuario puede listar/ver usuarios.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determina si el usuario puede ver el perfil de otro usuario.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determina si el usuario puede crear usuarios.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determina si el usuario puede actualizar usuarios.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determina si el usuario puede eliminar usuarios.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('Administrador') && $user->id !== $model->id;
    }
}
