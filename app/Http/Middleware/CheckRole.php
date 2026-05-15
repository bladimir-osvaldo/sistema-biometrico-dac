<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Mapa de alias -> nombre real del rol en la base de datos (Spatie).
     *
     * Permite usar tanto identificadores cortos en inglés (admin, coordinador,
     * docente) como los nombres canónicos en español (Administrador,
     * "Coordinador DAC", "Docente") dentro del middleware `role:...`.
     */
    protected const ROLE_ALIASES = [
        'admin'         => 'Administrador',
        'administrador' => 'Administrador',
        'coordinador'   => 'Coordinador DAC',
        'docente'       => 'Docente',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Verificar que haya un usuario autenticado
        if (!$user) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Normalizar los roles solicitados a sus nombres canónicos de Spatie
        $allowed = collect($roles)->map(function (string $role): string {
            return self::ROLE_ALIASES[strtolower($role)] ?? $role;
        })->unique();

        // El usuario debe tener al menos uno de los roles permitidos (vía Spatie)
        if (!$allowed->contains(fn (string $role) => $user->hasRole($role))) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
