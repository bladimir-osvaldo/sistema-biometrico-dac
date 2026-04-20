<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones de la base de datos para el Sistema Biométrico DAC.
     * Incluye tablas de roles/permisos Spatie y las tablas de negocio del sistema.
     */
    public function up(): void
    {
        // 0. Tablas de Spatie Permission (Roles y Permisos)
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });

        // 1. Modificación / Extensión de la tabla 'users' para perfil docente y datos biométricos
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni')->nullable()->unique()->after('email')->comment('Documento de Identidad del docente/usuario');
            $table->string('codigo_docente')->nullable()->unique()->after('dni')->comment('Código institucional único del docente');
            $table->string('telefono')->nullable()->after('codigo_docente')->comment('Teléfono de contacto');
            $table->string('carrera')->nullable()->after('telefono')->comment('Carrera / Departamento académico al que pertenece');
            $table->string('cargo')->default('Docente')->after('carrera')->comment('Cargo o título académico');
            $table->string('foto_perfil')->nullable()->after('cargo')->comment('Ruta relativa de la foto de perfil');
            $table->string('huella_template_hash')->nullable()->comment('Hash SHA-256 de la plantilla biométrica para verificación');
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'LICENCIA'])->default('ACTIVO')->after('huella_template_hash')->comment('Estado operativo del usuario');
        });

        // 2. Tabla de Dispositivos Biométricos IoT (ESP32)
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código identificador del dispositivo (Ej: ESP32-AULA-101)');
            $table->string('nombre')->comment('Nombre descriptivo del lector');
            $table->string('ubicacion_aula')->comment('Ubicación física o aula (Ej: Aula 204 - Bloque A)');
            $table->string('mac_address')->nullable()->unique()->comment('Dirección MAC del módulo Wi-Fi ESP32');
            $table->string('firmware_version')->default('1.0.0')->comment('Versión actual del firmware en el ESP32');
            $table->enum('estado', ['ONLINE', 'OFFLINE', 'MANTENIMIENTO'])->default('OFFLINE')->comment('Estado de comunicación del hardware');
            $table->timestamp('ultimo_heartbeat')->nullable()->comment('Última fecha/hora de comunicación activa');
            $table->string('api_token', 80)->unique()->comment('Token de autenticación Sanctum/Bearer para la API IoT');
            $table->timestamps();
        });

        // 3. Tabla de Horarios de Clases y Materias
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Docente asignado');
            $table->string('materia')->comment('Nombre de la materia o asignatura');
            $table->string('codigo_materia')->nullable()->comment('Código de la materia');
            $table->string('aula')->comment('Aula donde se imparte la clase');
            $table->enum('dia_semana', ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'])->comment('Día asignado');
            $table->time('hora_inicio')->comment('Hora planificada de inicio de clase');
            $table->time('hora_fin')->comment('Hora planificada de finalización');
            $table->integer('tolerancia_minutos')->default(10)->comment('Minutos de tolerancia antes de marcar tardanza');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 4. Tabla de Registros de Asistencia Biométrica
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Docente que marcó asistencia');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('set null')->comment('Horario correspondiente si aplica');
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null')->comment('Dispositivo ESP32 que capturó la huella');
            $table->date('fecha')->comment('Fecha del marcado (YYYY-MM-DD)');
            $table->time('hora_marcado')->comment('Hora exacta del marcado');
            $table->enum('estado', ['PUNTUAL', 'TARDANZA', 'FALTA_JUSTIFICADA', 'FALTA_INJUSTIFICADA'])->default('PUNTUAL')->comment('Clasificación de asistencia');
            $table->enum('modo_marcado', ['ONLINE', 'OFFLINE_SYNC', 'MANUAL_ADMIN'])->default('ONLINE')->comment('Modo en que se registró la marcación');
            $table->integer('minutos_retraso')->default(0)->comment('Minutos de retraso respecto a la hora de inicio');
            $table->string('ip_origen')->nullable()->comment('Dirección IP desde la que se registró el marcado');
            $table->text('observaciones')->nullable()->comment('Notas adicionales del sistema o administrador');
            $table->timestamps();

            // Índices para optimizar búsquedas y consultas pesadas de reportes
            $table->index(['fecha', 'user_id']);
            $table->index(['estado']);
        });

        // 5. Tabla de Justificaciones de Inasistencia / Tardanzas
        Schema::create('justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Docente solicitante');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('cascade')->comment('Asistencia relacionada');
            $table->date('fecha_inasistencia')->comment('Fecha que se justifica');
            $table->string('motivo')->comment('Título o motivo de la solicitud');
            $table->text('descripcion')->comment('Explicación detallada del motivo');
            $table->string('archivo_adjunto')->nullable()->comment('Ruta del comprobante subido (PDF, JPG, PNG)');
            $table->enum('estado', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])->default('PENDIENTE')->comment('Estado de revisión por Coordinador');
            $table->foreignId('revisado_por')->nullable()->constrained('users')->onDelete('set null')->comment('Coordinador que aprobó/rechazó');
            $table->text('comentario_coordinador')->nullable()->comment('Observaciones de la revisión');
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamps();
        });

        // 6. Tabla de Logs de Auditoría del Sistema
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuario que ejecutó la acción');
            $table->string('accion')->comment('Acción realizada (Ej: LOGIN, CREAR_USUARIO, SINCRONIZAR_ESP32)');
            $table->string('modulo')->comment('Módulo afectado (Auth, Dispositivos, Horarios, etc.)');
            $table->text('detalles')->nullable()->comment('Descripción o JSON de cambios');
            $table->string('ip_address', 45)->nullable()->comment('Dirección IP origen');
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('justifications');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'dni',
                'codigo_docente',
                'telefono',
                'carrera',
                'cargo',
                'foto_perfil',
                'huella_template_hash',
                'estado'
            ]);
        });
    }
};
