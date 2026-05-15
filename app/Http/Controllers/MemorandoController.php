<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador para la Generación Formal de Memorandos por Inasistencias.
 */
class MemorandoController extends Controller
{
    /**
     * Vista principal para selección de docente y parámetros del memorando.
     */
    public function index()
    {
        $docentes = User::role('Docente')->get();
        if ($docentes->isEmpty()) {
            $docentes = User::where('cargo', 'Docente')->orWhere('role', 'docente')->get();
        }

        return view('memorandos.index', compact('docentes'));
    }

    /**
     * Genera vista previa o descarga PDF del memorando.
     */
    public function generar(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            'tipo'        => 'required|string|in:RETRASO_REITERADO,INASISTENCIA_INJUSTIFICADA,INCUMPLIMIENTO_HORARIO',
            'accion'      => 'nullable|string|in:descargar,preview',
        ], [
            'user_id.required'     => 'Debe seleccionar un docente.',
            'fecha_desde.required' => 'La fecha de inicio es requerida.',
            'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
        ]);

        $docente = User::findOrFail($request->user_id);

        $incidencias = Attendance::with(['schedule', 'device'])
            ->where('user_id', $docente->id)
            ->whereBetween('fecha', [$request->fecha_desde, $request->fecha_hasta])
            ->whereIn('estado', ['TARDANZA', 'FALTA_INJUSTIFICADA'])
            ->orderBy('fecha', 'asc')
            ->get();

        $data = [
            'docente'       => $docente,
            'tipo'          => $request->tipo,
            'fecha_desde'   => $request->fecha_desde,
            'fecha_hasta'   => $request->fecha_hasta,
            'incidencias'   => $incidencias,
            'fecha_emision' => now()->format('Y-m-d'),
            'codigo_memo'   => 'MEMO-DAC-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) . '/' . date('Y'),
            'coordinador'   => Auth::user() ? Auth::user()->name : 'Coordinación Académica'
        ];

        // Registro de auditoría
        AuditLog::create([
            'user_id'    => Auth::id(),
            'accion'     => 'GENERAR_MEMORANDO',
            'modulo'     => 'Memorandos',
            'detalles'   => "Memorando emitido para {$docente->name} por " . Auth::user()->name,
            'ip_address' => $request->ip(),
        ]);

        if ($request->input('accion') === 'preview') {
            return view('reports.memorando_pdf', $data);
        }

        $pdf = Pdf::loadView('reports.memorando_pdf', $data);
        return $pdf->download("memorando_{$docente->dni}_" . date('Ymd') . ".pdf");
    }
}
