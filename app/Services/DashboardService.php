<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Justification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Servicio de Dashboard del Sistema Biométrico DAC.
 *
 * Centraliza el cálculo de KPIs, series de gráficos y consultas operativas,
 * con filtrado por período (mes/año) y por docente según el rol del usuario.
 */
class DashboardService
{
    /**
     * Estados que se consideran "falta".
     */
    protected const ESTADOS_FALTA = ['FALTA_INJUSTIFICADA', 'FALTA_JUSTIFICADA'];

    /**
     * Resuelve el alcance de usuario según el rol activo.
     *
     * - Docente: siempre su propio id (no puede ver datos de otros).
     * - Admin / Coordinador: permite el id solicitado o null (todos).
     */
    public function scopedUserId(?int $requestedId): ?int
    {
        $user = auth()->user();

        if ($user && $user->hasRole('Docente')) {
            return $user->id;
        }

        return $requestedId;
    }

    /**
     * Resuelve el período "YYYY-MM" a partir de mes y año (con validación).
     */
    public function resolvePeriod(?string $mes = null, ?int $anio = null): string
    {
        $anio = $anio ?? (int) date('Y');
        $mes = (int) ($mes ?? date('m'));

        // Limitar rangos válidos
        $anio = max(2020, min(2100, $anio));
        $mes = max(1, min(12, $mes));

        return sprintf('%04d-%02d', $anio, $mes);
    }

    /**
     * Aplica el filtro por período y opcionalmente por docente a una consulta de asistencias.
     */
    protected function scopedAttendanceQuery(string $period, ?int $userId = null)
    {
        $query = Attendance::query()
            ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$period]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    /**
     * Calcula los KPIs principales del período indicado.
     *
     * @return array{totalAsistencias:int,puntuales:int,tardanzas:int,faltas:int,porcentajePuntualidad:float}
     */
    public function kpis(?string $period = null, ?int $userId = null): array
    {
        $period = $period ?: $this->resolvePeriod();
        $userId = $this->scopedUserId($userId);

        $base = $this->scopedAttendanceQuery($period, $userId);

        $totalAsistencias = (clone $base)->count();
        $puntuales        = (clone $base)->where('estado', 'PUNTUAL')->count();
        $tardanzas        = (clone $base)->where('estado', 'TARDANZA')->count();
        $faltas           = (clone $base)->whereIn('estado', self::ESTADOS_FALTA)->count();

        $porcentajePuntualidad = $totalAsistencias > 0
            ? round(($puntuales / $totalAsistencias) * 100, 1)
            : 100.0;

        return compact('totalAsistencias', 'puntuales', 'tardanzas', 'faltas', 'porcentajePuntualidad');
    }

    /**
     * Genera la serie diaria (últimos N días hábiles) para el gráfico de barras.
     *
     * @return array{labels:array,puntuales:array,tardanzas:array,faltas:array}
     */
    public function weeklySeries(?int $userId = null, int $days = 7): array
    {
        $userId = $this->scopedUserId($userId);

        $labels    = [];
        $puntuales = [];
        $tardanzas = [];
        $faltas    = [];

        $diasEspanol = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        $procesados = 0;
        $offset     = 0;

        while ($procesados < $days) {
            $fecha = Carbon::today()->subDays($offset);

            // Solo días hábiles (lunes a viernes)
            if (!$fecha->isWeekend()) {
                $fechaStr = $fecha->toDateString();

                $query = Attendance::where('fecha', $fechaStr);
                if ($userId) {
                    $query->where('user_id', $userId);
                }

                array_unshift($labels, $diasEspanol[$fecha->dayOfWeek] . ' ' . $fecha->format('d/m'));
                array_unshift($puntuales, (clone $query)->where('estado', 'PUNTUAL')->count());
                array_unshift($tardanzas, (clone $query)->where('estado', 'TARDANZA')->count());
                array_unshift($faltas, (clone $query)->whereIn('estado', self::ESTADOS_FALTA)->count());

                $procesados++;
            }
            $offset++;
        }

        return compact('labels', 'puntuales', 'tardanzas', 'faltas');
    }

    /**
     * Devuelve las últimas marcaciones con eager loading.
     */
    public function ultimasAsistencias(?int $userId = null, int $limit = 10): Collection
    {
        $userId = $this->scopedUserId($userId);

        $query = Attendance::with(['user', 'device', 'schedule'])
            ->latest('fecha')
            ->latest('hora_marcado')
            ->limit($limit);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Docentes con 3 o más tardanzas en el período indicado (alertas).
     */
    public function docentesConAlertas(?string $period = null): Collection
    {
        $period = $period ?: $this->resolvePeriod();

        return User::whereHas('attendances', function ($q) use ($period) {
            $q->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$period])
              ->where('estado', 'TARDANZA');
        })
        ->withCount(['attendances as tardanzas_mes_count' => function ($q) use ($period) {
            $q->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$period])
              ->where('estado', 'TARDANZA');
        }])
        ->having('tardanzas_mes_count', '>=', 3)
        ->get();
    }

    /**
     * Justificaciones pendientes de revisión (visible para Admin y Coordinador).
     */
    public function justificacionesPendientes(): int
    {
        return Justification::where('estado', 'PENDIENTE')->count();
    }

    /**
     * Lista de docentes (rol "Docente") para los buscadores y reportes.
     */
    public function docentes(): Collection
    {
        $docentes = User::role('Docente')->get();

        // Fallback por si el rol de Spatie no está asignado
        if ($docentes->isEmpty()) {
            $docentes = User::where('cargo', 'Docente')->orWhere('role', 'Docente')->get();
        }

        return $docentes;
    }

    /**
     * Búsqueda de docentes por nombre, DNI o código (para el autocompletar).
     */
    public function buscarDocentes(string $q, int $limit = 10): Collection
    {
        $q = trim($q);

        if ($q === '') {
            return $this->docentes()->take($limit)->values();
        }

        return User::role('Docente')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('dni', 'like', "%{$q}%")
                      ->orWhere('codigo_docente', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit($limit)
            ->get();
    }
}
