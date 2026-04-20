<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Device;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Justification;
use App\Models\AuditLog;
use Carbon\Carbon;

/**
 * Seeder principal del Sistema Biométrico DAC.
 * Carga roles, usuarios, horarios oficiales, dispositivos y datos de prueba.
 *
 * Horarios Oficiales DAC - Unidad Educativa Tiquipaya:
 *   - Turno 1: 13:15 a 14:20 (Lunes a Viernes)
 *   - Turno 2: 14:30 a 15:30 (Lunes a Viernes)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Carga inicial de la base de datos del Sistema Biométrico DAC.
     */
    public function run(): void
    {
        // ========================================================
        // 1. CREACIÓN DE ROLES DE SISTEMA (Spatie Permission)
        // ========================================================
        $roleAdmin   = Role::firstOrCreate(['name' => 'Administrador',  'guard_name' => 'web']);
        $roleCoord   = Role::firstOrCreate(['name' => 'Coordinador DAC', 'guard_name' => 'web']);
        $roleDocente = Role::firstOrCreate(['name' => 'Docente',         'guard_name' => 'web']);

        // ========================================================
        // 2. USUARIO ADMINISTRADOR DEL SISTEMA
        // ========================================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@dac.edu.bo'],
            [
                'name'           => 'Ing. Roberto Mendoza Vásquez',
                'password'       => Hash::make('Admin@2024!'),
                'dni'            => '1234567',
                'codigo_docente' => 'ADM-001',
                'telefono'       => '71234567',
                'carrera'        => 'Dirección de Tecnología e Innovación',
                'cargo'          => 'Administrador de Sistemas',
                'estado'         => 'ACTIVO',
            ]
        );
        $admin->assignRole($roleAdmin);

        // ========================================================
        // 3. USUARIO COORDINADOR DAC
        // ========================================================
        $coord = User::firstOrCreate(
            ['email' => 'coordinador@dac.edu.bo'],
            [
                'name'           => 'Lic. Carlos Eduardo Alarcón Paz',
                'password'       => Hash::make('Coord@2024!'),
                'dni'            => '2345678',
                'codigo_docente' => 'CRD-002',
                'telefono'       => '72345678',
                'carrera'        => 'Coordinación Académica DAC',
                'cargo'          => 'Coordinador General del Programa',
                'estado'         => 'ACTIVO',
            ]
        );
        $coord->assignRole($roleCoord);

        // ========================================================
        // 4. DOCENTES DEL PROGRAMA DAC
        // ========================================================
        $docente1 = User::firstOrCreate(
            ['email' => 'mguterrez@dac.edu.bo'],
            [
                'name'                   => 'Dra. María Elena Gutiérrez Flores',
                'password'               => Hash::make('Docente@2024!'),
                'dni'                    => '7654321',
                'codigo_docente'         => 'DOC-1001',
                'telefono'               => '76543210',
                'carrera'                => 'Ingeniería de Sistemas e Informática',
                'cargo'                  => 'Docente Titular Tiempo Completo',
                'huella_template_hash'   => hash('sha256', 'fingerprint_template_mguterrez_1001'),
                'estado'                 => 'ACTIVO',
            ]
        );
        $docente1->assignRole($roleDocente);

        $docente2 = User::firstOrCreate(
            ['email' => 'fsilva@dac.edu.bo'],
            [
                'name'                   => 'Ing. Fernando Alejandro Silva Ríos',
                'password'               => Hash::make('Docente@2024!'),
                'dni'                    => '8901234',
                'codigo_docente'         => 'DOC-1002',
                'telefono'               => '77890123',
                'carrera'                => 'Ingeniería en Telecomunicaciones y Redes',
                'cargo'                  => 'Docente Tiempo Completo',
                'huella_template_hash'   => hash('sha256', 'fingerprint_template_fsilva_1002'),
                'estado'                 => 'ACTIVO',
            ]
        );
        $docente2->assignRole($roleDocente);

        $docente3 = User::firstOrCreate(
            ['email' => 'pmorales@dac.edu.bo'],
            [
                'name'                   => 'Msc. Patricia Cristina Morales Soria',
                'password'               => Hash::make('Docente@2024!'),
                'dni'                    => '4567890',
                'codigo_docente'         => 'DOC-1003',
                'telefono'               => '70123456',
                'carrera'                => 'Ingeniería Industrial y Producción',
                'cargo'                  => 'Docente Contratado',
                'huella_template_hash'   => hash('sha256', 'fingerprint_template_pmorales_1003'),
                'estado'                 => 'ACTIVO',
            ]
        );
        $docente3->assignRole($roleDocente);

        $docente4 = User::firstOrCreate(
            ['email' => 'jramirez@dac.edu.bo'],
            [
                'name'                   => 'Ing. Juan Pablo Ramírez Torres',
                'password'               => Hash::make('Docente@2024!'),
                'dni'                    => '5678901',
                'codigo_docente'         => 'DOC-1004',
                'telefono'               => '71234568',
                'carrera'                => 'Ingeniería Mecatrónica',
                'cargo'                  => 'Docente Titular',
                'huella_template_hash'   => hash('sha256', 'fingerprint_template_jramirez_1004'),
                'estado'                 => 'ACTIVO',
            ]
        );
        $docente4->assignRole($roleDocente);

        $docente5 = User::firstOrCreate(
            ['email' => 'lvargas@dac.edu.bo'],
            [
                'name'                   => 'Dr. Luis Alberto Vargas Mendoza',
                'password'               => Hash::make('Docente@2024!'),
                'dni'                    => '6789012',
                'codigo_docente'         => 'DOC-1005',
                'telefono'               => '72345679',
                'carrera'                => 'Matemáticas Aplicadas y Física',
                'cargo'                  => 'Docente Horario',
                'huella_template_hash'   => hash('sha256', 'fingerprint_template_lvargas_1005'),
                'estado'                 => 'ACTIVO',
            ]
        );
        $docente5->assignRole($roleDocente);

        // ========================================================
        // 5. DISPOSITIVO ESP32 BIOMÉTRICO
        // ========================================================
        $device = Device::firstOrCreate(
            ['codigo' => 'ESP32-DAC-001'],
            [
                'nombre'            => 'Lector Biométrico Principal – Aula DAC',
                'ubicacion_aula'    => 'Sala DAC – Unidad Educativa Tiquipaya',
                'mac_address'       => '24:0A:C4:DA:C0:01',
                'firmware_version'  => '2.1.0',
                'estado'            => 'ONLINE',
                'ultimo_heartbeat'  => now(),
                'api_token'        => 'dac_esp32_bearer_token_secure_2024',
            ]
        );

        $device2 = Device::firstOrCreate(
            ['codigo' => 'ESP32-DAC-002'],
            [
                'nombre'            => 'Lector Biométrico Secundario – Laboratorio',
                'ubicacion_aula'    => 'Laboratorio de Cómputo – Unidad Educativa Tiquipaya',
                'mac_address'       => '24:0A:C4:DA:C0:02',
                'firmware_version'  => '2.1.0',
                'estado'            => 'OFFLINE',
                'ultimo_heartbeat'  => now()->subHours(3),
                'api_token'        => 'dac_esp32_bearer_token_secure_lab2024',
            ]
        );

        // ========================================================
        // 6. HORARIOS OFICIALES DAC
        //    Turno 1: 13:15 – 14:20 | Turno 2: 14:30 – 15:30
        //    De Lunes a Viernes – Unidad Educativa Tiquipaya
        // ========================================================
        $dias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];

        // Turno 1: 13:15 a 14:20 – Docente 1 (Arquitectura de Software IoT)
        $horariosTurno1 = [];
        foreach ($dias as $dia) {
            $horariosTurno1[$dia] = Schedule::firstOrCreate(
                [
                    'user_id'        => $docente1->id,
                    'dia_semana'     => $dia,
                    'hora_inicio'    => '13:15:00',
                ],
                [
                    'materia'            => 'Arquitectura de Software para IoT',
                    'codigo_materia'     => 'SIS-401',
                    'aula'               => 'Sala DAC – Unidad Educativa Tiquipaya',
                    'hora_fin'           => '14:20:00',
                    'tolerancia_minutos' => 10,
                    'activo'             => true,
                ]
            );
        }

        // Turno 2: 14:30 a 15:30 – Docente 2 (Redes de Computadoras II)
        $horariosTurno2 = [];
        foreach ($dias as $dia) {
            $horariosTurno2[$dia] = Schedule::firstOrCreate(
                [
                    'user_id'        => $docente2->id,
                    'dia_semana'     => $dia,
                    'hora_inicio'    => '14:30:00',
                ],
                [
                    'materia'            => 'Redes de Computadoras Avanzadas',
                    'codigo_materia'     => 'TEL-302',
                    'aula'               => 'Laboratorio de Cómputo – Unidad Educativa Tiquipaya',
                    'hora_fin'           => '15:30:00',
                    'tolerancia_minutos' => 10,
                    'activo'             => true,
                ]
            );
        }

        // Turno 1 – Docente 3 (Gestión de Calidad Industrial – solo Lun/Mié/Vie)
        foreach (['LUNES', 'MIERCOLES', 'VIERNES'] as $dia) {
            Schedule::firstOrCreate(
                [
                    'user_id'        => $docente3->id,
                    'dia_semana'     => $dia,
                    'hora_inicio'    => '13:15:00',
                ],
                [
                    'materia'            => 'Gestión de Calidad Industrial',
                    'codigo_materia'     => 'IND-505',
                    'aula'               => 'Sala DAC – Unidad Educativa Tiquipaya',
                    'hora_fin'           => '14:20:00',
                    'tolerancia_minutos' => 10,
                    'activo'             => true,
                ]
            );
        }

        // Turno 2 – Docente 4 (Robótica y Mecatrónica – solo Mar/Jue)
        foreach (['MARTES', 'JUEVES'] as $dia) {
            Schedule::firstOrCreate(
                [
                    'user_id'        => $docente4->id,
                    'dia_semana'     => $dia,
                    'hora_inicio'    => '14:30:00',
                ],
                [
                    'materia'            => 'Robótica y Mecatrónica Aplicada',
                    'codigo_materia'     => 'MEC-601',
                    'aula'               => 'Laboratorio de Cómputo – Unidad Educativa Tiquipaya',
                    'hora_fin'           => '15:30:00',
                    'tolerancia_minutos' => 10,
                    'activo'             => true,
                ]
            );
        }

        // Turno 1 – Docente 5 (Matemáticas Aplicadas – Lun a Vie)
        foreach ($dias as $dia) {
            Schedule::firstOrCreate(
                [
                    'user_id'        => $docente5->id,
                    'dia_semana'     => $dia,
                    'hora_inicio'    => '13:15:00',
                ],
                [
                    'materia'            => 'Matemáticas Aplicadas a la Ingeniería',
                    'codigo_materia'     => 'MAT-201',
                    'aula'               => 'Sala DAC – Unidad Educativa Tiquipaya',
                    'hora_fin'           => '14:20:00',
                    'tolerancia_minutos' => 10,
                    'activo'             => true,
                ]
            );
        }

        // ========================================================
        // 7. REGISTROS DE ASISTENCIA DE PRUEBA (últimas 2 semanas)
        //    Para alimentar los gráficos del Dashboard
        // ========================================================
        $primerHorarioTurno1 = $horariosTurno1['LUNES'] ?? null;
        $primerHorarioTurno2 = $horariosTurno2['LUNES'] ?? null;

        for ($i = 10; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);

            // Saltar fines de semana
            if ($fecha->isWeekend()) {
                continue;
            }

            // Asistencia Docente 1 – Turno 1 (13:15)
            if ($primerHorarioTurno1) {
                $puntual1 = ($i % 3 !== 1); // algunos tardanzas
                Attendance::create([
                    'user_id'         => $docente1->id,
                    'schedule_id'     => $primerHorarioTurno1->id,
                    'device_id'       => $device->id,
                    'fecha'           => $fecha->toDateString(),
                    'hora_marcado'    => $puntual1 ? '13:12:00' : '13:28:00',
                    'estado'          => $puntual1 ? 'PUNTUAL' : 'TARDANZA',
                    'modo_marcado'    => 'ONLINE',
                    'minutos_retraso' => $puntual1 ? 0 : 13,
                    'ip_origen'       => '192.168.1.50',
                    'observaciones'   => $puntual1 ? 'Marcación biométrica exitosa' : 'Tardanza registrada por sistema',
                ]);
            }

            // Asistencia Docente 2 – Turno 2 (14:30)
            if ($primerHorarioTurno2) {
                $puntual2 = ($i % 4 !== 2);
                Attendance::create([
                    'user_id'         => $docente2->id,
                    'schedule_id'     => $primerHorarioTurno2->id,
                    'device_id'       => $device->id,
                    'fecha'           => $fecha->toDateString(),
                    'hora_marcado'    => $puntual2 ? '14:27:00' : '14:45:00',
                    'estado'          => $puntual2 ? 'PUNTUAL' : 'TARDANZA',
                    'modo_marcado'    => ($i % 3 === 0) ? 'OFFLINE_SYNC' : 'ONLINE',
                    'minutos_retraso' => $puntual2 ? 0 : 15,
                    'ip_origen'       => '192.168.1.50',
                    'observaciones'   => (!$puntual2) ? 'Sincronizado desde EEPROM del ESP32' : null,
                ]);
            }
        }

        // ========================================================
        // 8. JUSTIFICACIÓN DE EJEMPLO (tardanza de Docente 2)
        // ========================================================
        Justification::firstOrCreate(
            [
                'user_id'           => $docente2->id,
                'fecha_inasistencia' => Carbon::today()->subDays(3)->toDateString(),
            ],
            [
                'motivo'             => 'Inconveniente vehicular en acceso principal',
                'descripcion'        => 'Bloqueo de la vía principal por trabajos municipales. Adjunto fotografías como respaldo. Solicito considerar marcado como puntual.',
                'estado'             => 'PENDIENTE',
            ]
        );

        // ========================================================
        // 9. REGISTRO DE AUDITORÍA INICIAL
        // ========================================================
        AuditLog::create([
            'user_id'    => $admin->id,
            'accion'     => 'INICIALIZACION_SISTEMA',
            'modulo'     => 'Sistema',
            'detalles'   => 'Carga inicial completada: roles, usuarios, horarios DAC (13:15-14:20 / 14:30-15:30), dispositivos ESP32 y datos de prueba.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->command->info('✅ Sistema Biométrico DAC – Base de datos inicializada correctamente.');
        $this->command->info('   Horarios DAC: Turno 1 (13:15-14:20) | Turno 2 (14:30-15:30) | Lunes a Viernes');
        $this->command->info('   Credenciales de acceso:');
        $this->command->info('   admin@dac.edu.bo        → Admin@2024!');
        $this->command->info('   coordinador@dac.edu.bo  → Coord@2024!');
        $this->command->info('   mguterrez@dac.edu.bo    → Docente@2024!');
    }
}
