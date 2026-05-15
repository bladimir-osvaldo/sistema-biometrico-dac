<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador API AJAX para el Dashboard.
 *
 * Endpoints:
 *  - GET /api/search/docentes?q=...          (autocompletar docentes)
 *  - GET /api/reports/attendance?user_id=&start=&end=   (serie JSON para gráficos)
 */
class DashboardApiController extends Controller
{
    protected DashboardService $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    /**
     * Busca docentes (rol "Docente") por nombre, DNI, código o correo.
     * Solo visible para Administrador y Coordinador.
     */
    public function searchDocentes(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('Docente')) {
            abort(403, 'No tienes permisos para buscar a otros docentes.');
        }

        $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $docentes = $this->service->buscarDocentes($request->input('q', ''), 10)
            ->map(fn ($d) => [
                'id'             => $d->id,
                'name'           => $d->name,
                'email'          => $d->email,
                'dni'            => $d->dni,
                'codigo_docente' => $d->codigo_docente,
            ]);

        return response()->json([
            'success'  => true,
            'docentes' => $docentes,
        ]);
    }

    /**
     * Genera la serie diaria de asistencias (puntuales/tardanzas/faltas)
     * para un docente y rango de fechas, en formato JSON para Chart.js.
     */
    public function attendanceReport(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'start'   => 'nullable|date',
            'end'     => 'nullable|date|after_or_equal:start',
        ]);

        $userId = $this->service->scopedUserId($request->integer('user_id'));
        $start  = $request->date('start') ?: Carbon::today()->subDays(6);
        $end    = $request->date('end') ?: Carbon::today();

        // Máximo 62 días por solicitud para evitar sobrecarga
        if ($start->diffInDays($end) > 62) {
            return response()->json([
                'success' => false,
                'message' => 'El rango de fechas no puede superar los 62 días.',
            ], 422);
        }

        $labels    = [];
        $puntuales = [];
        $tardanzas = [];
        $faltas    = [];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $fecha = $d->toDateString();

            $query = Attendance::where('fecha', $fecha);
            if ($userId) {
                $query->where('user_id', $userId);
            }

            $labels[]    = $d->isoFormat('ddd DD/MM');
            $puntuales[] = (clone $query)->where('estado', 'PUNTUAL')->count();
            $tardanzas[] = (clone $query)->where('estado', 'TARDANZA')->count();
            $faltas[]    = (clone $query)->whereIn('estado', ['FALTA_INJUSTIFICADA', 'FALTA_JUSTIFICADA'])->count();
        }

        return response()->json([
            'success'    => true,
            'user_id'    => $userId,
            'labels'     => $labels,
            'puntuales'  => $puntuales,
            'tardanzas'  => $tardanzas,
            'faltas'     => $faltas,
        ]);
    }
}
