<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

/**
 * Controlador de Gestión y Exportación de Asistencias.
 */
class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $user = auth()->user();

        $query = Attendance::with(['user', 'schedule', 'device']);

        // Alcance por rol: Docente solo sus registros | Coordinador solo sus coordinados
        if ($user->hasRole('Docente')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Coordinador DAC')) {
            $query->whereHas('user', fn ($q) => $q->where('coordinador_id', $user->id));
        }

        // Filtro por docente
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por fecha desde/hasta
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        // Filtro por estado (uno o varios estados: ?estado=... o ?estados[]=...)
        if ($request->filled('estados') && is_array($request->estados)) {
            $query->whereIn('estado', $request->estados);
        } elseif ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $attendances = $query->latest()->paginate(20);
        $docentes = $this->docentesVisibles();

        return view('attendances.index', compact('attendances', 'docentes'));
    }

    /**
     * Exporta el reporte de asistencias a formato PDF.
     */
    public function exportPdf(Request $request)
    {
        $this->authorize('export', Attendance::class);

        $query = $this->scopedQuery($request);

        $attendances = $query->latest()->get();
        $pdf = Pdf::loadView('reports.attendance_pdf', compact('attendances'));
        return $pdf->download('reporte_asistencias_dac_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exporta el reporte de asistencias a formato Excel.
     */
    public function exportExcel(Request $request)
    {
        $this->authorize('export', Attendance::class);

        return Excel::download(new AttendanceExport($request), 'reporte_asistencias_dac_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Construye la consulta con alcance por rol y filtros de fecha/docente.
     */
    protected function scopedQuery(Request $request)
    {
        $user = auth()->user();

        $query = Attendance::with(['user', 'schedule', 'device']);

        if ($user->hasRole('Docente')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Coordinador DAC')) {
            $query->whereHas('user', fn ($q) => $q->where('coordinador_id', $user->id));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        return $query;
    }

    /**
     * Docentes visibles según el rol: Admin todos | Coordinador sus coordinados | Docente solo él mismo.
     */
    protected function docentesVisibles()
    {
        $user = auth()->user();

        if ($user->hasRole('Docente')) {
            return collect([$user]);
        }

        $docentes = User::role('Docente');

        if ($user->hasRole('Coordinador DAC')) {
            $docentes->where('coordinador_id', $user->id);
        }

        return $docentes->get();
    }
}
