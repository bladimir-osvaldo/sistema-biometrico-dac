<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

/**
 * Seeder de Permisos y Roles del Sistema Biométrico DAC.
 *
 * Establece las capacidades de cada rol (Spatie Permission) y asigna
 * docentes a coordinadores para el control de horarios coordinados.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Permisos del sistema y roles que los poseen.
     */
    protected array $permissions = [
        'gestionar usuarios'            => ['Administrador'],
        'gestionar dispositivos'        => ['Administrador'],
        'gestionar huellas'             => ['Administrador'],
        'configuracion sistema'         => ['Administrador'],
        'ver monitoreo'                 => ['Administrador'],
        'gestionar horarios'            => ['Administrador'],
        'gestionar horarios coordinados'=> ['Administrador', 'Coordinador DAC'],
        'aprobar justificaciones'       => ['Administrador', 'Coordinador DAC'],
        'ver memorandos'                => ['Administrador', 'Coordinador DAC'],
        'ver reportes globales'         => ['Administrador', 'Coordinador DAC'],
    ];

    public function run(): void
    {
        // 1. Crear permisos
        foreach (array_keys($this->permissions) as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Sincronizar permisos a los roles
        foreach ($this->permissions as $perm => $roles) {
            $permission = Permission::findByName($perm);
            foreach ($roles as $roleName) {
                $role = Role::findByName($roleName);
                if ($role) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        // 3. Asignación de docentes a coordinadores (datos de demostración)
        $coordPrincipal = User::where('email', 'coordinador@dac.edu.bo')->first();
        $coordSecundario = User::where('email', 'pmorales@dac.edu.bo')->first();

        if ($coordPrincipal) {
            User::whereIn('email', [
                'mguterrez@dac.edu.bo',
                'fsilva@dac.edu.bo',
                'jramirez@dac.edu.bo',
            ])->update(['coordinador_id' => $coordPrincipal->id]);
        }

        if ($coordSecundario) {
            User::whereIn('email', [
                'lvargas@dac.edu.bo',
            ])->update(['coordinador_id' => $coordSecundario->id]);
        }

        $this->command->info('✅ Roles y permisos sincronizados correctamente.');
    }
}
