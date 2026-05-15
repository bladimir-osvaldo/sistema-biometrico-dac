<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\SchedulePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class       => UserPolicy::class,
        Attendance::class => AttendancePolicy::class,
        Schedule::class   => SchedulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Puerta implícita: el Admin pasa cualquier comprobación y los permisos
        // de Spatie pueden usarse con @can / ->can() en vistas y controladores.
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Administrador')) {
                return true;
            }

            try {
                if ($user->hasPermissionTo($ability)) {
                    return true;
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                // No es un permiso de Spatie: se deja que la Policy resuelva.
            }

            return null;
        });
    }
}
