<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\AuditLog;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Gestión de Horarios de Clases del Sistema Biométrico DAC.
 * Aplica autorización por Policies (Spatie) y restricción de coordinación.
 */
class ScheduleController extends Controller
{
    /**
     * Muestra la lista paginada de horarios asignados con eager loading.
     * Admin: todos | Coordinador: solo docentes que coordina | Docente: bloqueado (usa "Mi Horario").
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Schedule::class);

        $user = auth()->user();

        $query = Schedule::with('user');

        // Coordinador: solo horarios de los docentes que coordina
        if ($user->hasRole('Coordinador DAC')) {
            $query->whereHas('user', fn ($q) => $q->where('coordinador_id', $user->id));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('dia_semana')) {
            $query->where('dia_semana', $request->dia_semana);
        }

        $schedules = $query->latest()->paginate(15);
        $docentes  = $this->docentesVisibles();

        return view('schedules.index', compact('schedules', 'docentes'));
    }

    /**
     * Muestra el formulario para crear un nuevo horario.
     */
    public function create()
    {
        $this->authorize('create', [Schedule::class, null]);

        $docentes = $this->docentesVisibles();
        return view('schedules.create', compact('docentes'));
    }

    /**
     * Guarda un nuevo horario usando DB::transaction y FormRequest.
     */
    public function store(StoreScheduleRequest $request)
    {
        $this->authorize('create', [Schedule::class, $request->user_id]);

        $schedule = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $validated['activo'] = true;

            $sch = Schedule::create($validated);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'CREAR_HORARIO',
                'modulo'     => 'Horarios',
                'detalles'   => "Horario creado para {$sch->materia} en {$sch->aula} ({$sch->dia_semana})",
                'ip_address' => $request->ip(),
            ]);

            return $sch;
        });

        return redirect()->route('schedules.index')
            ->with('success', "✅ Horario de «{$schedule->materia}» asignado exitosamente.");
    }

    /**
     * Muestra la información detallada de un horario.
     */
    public function show(Schedule $schedule)
    {
        $this->authorize('view', $schedule);

        $schedule->load(['user', 'attendances' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('schedules.show', compact('schedule'));
    }

    /**
     * Muestra el formulario para editar un horario.
     */
    public function edit(Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        $docentes = $this->docentesVisibles();
        return view('schedules.edit', compact('schedule', 'docentes'));
    }

    /**
     * Actualiza un horario usando DB::transaction.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $this->authorize('update', $schedule);

        DB::transaction(function () use ($request, $schedule) {
            $schedule->update($request->validated());

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'ACTUALIZAR_HORARIO',
                'modulo'     => 'Horarios',
                'detalles'   => "Horario de {$schedule->materia} actualizado por " . Auth::user()->name,
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('schedules.index')
            ->with('success', "✅ Horario de «{$schedule->materia}» actualizado correctamente.");
    }

    /**
     * Elimina un horario usando DB::transaction.
     */
    public function destroy(Request $request, Schedule $schedule)
    {
        $this->authorize('delete', $schedule);

        $materia = $schedule->materia;

        DB::transaction(function () use ($request, $schedule, $materia) {
            $schedule->delete();

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'ELIMINAR_HORARIO',
                'modulo'     => 'Horarios',
                'detalles'   => "Horario de la materia {$materia} eliminado por " . Auth::user()->name,
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('schedules.index')
            ->with('success', "🗑️ Horario de «{$materia}» eliminado exitosamente.");
    }

    /**
     * Muestra el horario propio del Docente autenticado.
     * Ruta: /mi-horario
     */
    public function misHorarios()
    {
        $user = auth()->user();

        $schedules = Schedule::with('user')
            ->where('user_id', $user->id)
            ->orderByRaw("FIELD(dia_semana, 'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO')")
            ->orderBy('hora_inicio')
            ->get();

        return view('schedules.mine', compact('schedules'));
    }

    /**
     * Devuelve los docentes visibles para la gestión de horarios según el rol:
     * Admin: todos | Coordinador: solo los que coordina.
     */
    protected function docentesVisibles()
    {
        $user = auth()->user();

        $docentes = User::role('Docente');

        if ($user->hasRole('Coordinador DAC')) {
            $docentes->where('coordinador_id', $user->id);
        }

        return $docentes->orderBy('name')->get();
    }
}
