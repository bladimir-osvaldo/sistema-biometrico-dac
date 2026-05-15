<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Justification;
use App\Models\Attendance;
use App\Models\AuditLog;

/**
 * Controlador de Justificaciones de Inasistencia / Tardanzas.
 */
class JustificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Docente')) {
            $justifications = Justification::with(['attendance', 'reviewer'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15);
        } else {
            $justifications = Justification::with(['user', 'attendance', 'reviewer'])
                ->latest()
                ->paginate(15);
        }

        return view('justifications.index', compact('justifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inasistencia' => 'required|date',
            'motivo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'archivo' => 'nullable|file|mimes:pdf,jpg,png|max:4096',
        ]);

        $filePath = null;
        if ($request->hasFile('archivo')) {
            $filePath = $request->file('archivo')->store('justificaciones', 'public');
        }

        Justification::create([
            'user_id' => auth()->id(),
            'attendance_id' => $request->attendance_id,
            'fecha_inasistencia' => $request->fecha_inasistencia,
            'motivo' => $request->motivo,
            'descripcion' => $request->descripcion,
            'archivo_adjunto' => $filePath,
            'estado' => 'PENDIENTE',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'accion' => 'SOLICITAR_JUSTIFICACION',
            'modulo' => 'Justificaciones',
            'detalles' => "Justificación solicitada para la fecha {$request->fecha_inasistencia}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('justifications.index')->with('success', 'Solicitud de justificación enviada correctamente.');
    }

    public function review(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:APROBADO,RECHAZADO',
            'comentario_coordinador' => 'nullable|string',
        ]);

        $justification = Justification::findOrFail($id);
        $justification->update([
            'estado' => $request->estado,
            'revisado_por' => auth()->id(),
            'comentario_coordinador' => $request->comentario_coordinador,
            'fecha_revision' => now(),
        ]);

        // Si fue aprobada y tenía asistencia asociada, cambiar estado a FALTA_JUSTIFICADA
        if ($request->estado === 'APROBADO' && $justification->attendance_id) {
            $attendance = Attendance::find($justification->attendance_id);
            if ($attendance) {
                $attendance->update(['estado' => 'FALTA_JUSTIFICADA']);
            }
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'accion' => 'REVISAR_JUSTIFICACION',
            'modulo' => 'Justificaciones',
            'detalles' => "Justificación #{$id} marcada como {$request->estado}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('justifications.index')->with('success', "Justificación {$request->estado} exitosamente.");
    }
}
