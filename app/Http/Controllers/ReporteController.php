<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Carbon\Carbon;

/**
 * Controlador de Reportes Avanzados del Sistema Biométrico DAC.
 */
class ReporteController extends Controller
{
    /**
     * Genera el reporte mensual en PDF de un docente específico.
     */
    public function reporteMensual(Request $request, $docente_id = null, $mes = null, $anio = null)
    {
        $docenteId = $docente_id ?? $request->input('docente_id');
        $mes = $mes ?? $request->input('mes', date('m'));
        $anio = $anio ?? $request->input('anio', date('Y'));

        $docente = User::findOrFail($docenteId);

        $fechaInicio = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $fechaFin = (clone $fechaInicio)->endOfMonth();

        $attendances = Attendance::with(['schedule', 'device'])
            ->where('user_id', $docenteId)
            ->whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->orderBy('fecha', 'asc')
            ->get();

        $totalPuntuales = $attendances->where('estado', 'PUNTUAL')->count();
        $totalTardanzas = $attendances->where('estado', 'TARDANZA')->count();
        $totalFaltas    = $attendances->whereIn('estado', ['FALTA_INJUSTIFICADA', 'FALTA_JUSTIFICADA'])->count();
        $totalJustificadas = $attendances->where('estado', 'FALTA_JUSTIFICADA')->count();

        $data = [
            'docente'           => $docente,
            'mes'               => $mes,
            'anio'              => $anio,
            'nombreMes'         => $fechaInicio->translatedFormat('F'),
            'attendances'       => $attendances,
            'totalPuntuales'    => $totalPuntuales,
            'totalTardanzas'    => $totalTardanzas,
            'totalFaltas'       => $totalFaltas,
            'totalJustificadas' => $totalJustificadas,
            'fechaEmision'      => date('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('reports.reporte_mensual_pdf', $data);
        return $pdf->download("reporte_mensual_{$docente->dni}_{$mes}_{$anio}.pdf");
    }

    /**
     * Genera el reporte exclusivo de tardanzas en PDF.
     */
    public function reporteTardanzas(Request $request, $docente_id = null, $mes = null, $anio = null)
    {
        $docenteId = $docente_id ?? $request->input('docente_id');
        $mes = $mes ?? $request->input('mes', date('m'));
        $anio = $anio ?? $request->input('anio', date('Y'));

        $docente = User::findOrFail($docenteId);

        $fechaInicio = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $fechaFin = (clone $fechaInicio)->endOfMonth();

        $tardanzas = Attendance::with(['schedule', 'device'])
            ->where('user_id', $docenteId)
            ->where('estado', 'TARDANZA')
            ->whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->orderBy('fecha', 'asc')
            ->get();

        $data = [
            'docente'      => $docente,
            'mes'          => $mes,
            'anio'         => $anio,
            'nombreMes'    => $fechaInicio->translatedFormat('F'),
            'tardanzas'    => $tardanzas,
            'totalMinutos' => $tardanzas->sum('minutos_retraso'),
            'fechaEmision' => date('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('reports.reporte_tardanzas_pdf', $data);
        return $pdf->download("reporte_tardanzas_{$docente->dni}_{$mes}_{$anio}.pdf");
    }

    /**
     * Exporta los registros de asistencia del docente a Excel.
     */
    public function exportarExcel(Request $request, $docente_id = null, $mes = null, $anio = null)
    {
        $docenteId = $docente_id ?? $request->input('docente_id');
        $mes = $mes ?? $request->input('mes', date('m'));
        $anio = $anio ?? $request->input('anio', date('Y'));

        $request->merge([
            'user_id' => $docenteId,
            'fecha_inicio' => Carbon::createFromDate($anio, $mes, 1)->startOfMonth()->toDateString(),
            'fecha_fin' => Carbon::createFromDate($anio, $mes, 1)->endOfMonth()->toDateString(),
        ]);

        return Excel::download(new AttendanceExport($request), "asistencias_docente_{$docenteId}_{$mes}_{$anio}.xlsx");
    }
}
