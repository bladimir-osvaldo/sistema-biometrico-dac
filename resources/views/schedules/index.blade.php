@extends('layouts.app')

@section('title', 'Gestión de Horarios & Aulas')

@section('content')
<div class="card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="fa-solid fa-calendar-days"></i> Programación de Clases y Horarios Académicos
        </div>
        <button class="btn-dac" onclick="document.getElementById('modalSchedule').style.display='flex'">
            <i class="fa-solid fa-plus"></i> Asignar Nuevo Horario
        </button>
    </div>

    <div class="table-responsive">
        <table class="dac-table">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Materia</th>
                    <th>Aula</th>
                    <th>Día</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Tolerancia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                <tr>
                    <td>
                        <strong>{{ $schedule->user->name ?? 'Docente no hallado' }}</strong><br>
                        <small style="color:var(--text-muted);">{{ $schedule->user->codigo_docente ?? '' }}</small>
                    </td>
                    <td><strong style="color:var(--color-guindo);">{{ $schedule->materia }}</strong> ({{ $schedule->codigo_materia ?? 'N/A' }})</td>
                    <td><span class="badge badge-info">{{ $schedule->aula }}</span></td>
                    <td><strong>{{ $schedule->dia_semana }}</strong></td>
                    <td>{{ $schedule->hora_inicio }}</td>
                    <td>{{ $schedule->hora_fin }}</td>
                    <td>{{ $schedule->tolerancia_minutos }} min</td>
                    <td>
                        <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar horario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer;" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $schedules->links() }}
    </div>
</div>

<!-- MODAL ASIGNAR HORARIO -->
<div id="modalSchedule" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); width:100%; max-width:500px; padding:30px; border-radius:12px; box-shadow:var(--card-shadow); border-top:5px solid var(--color-guindo);">
        <h3 style="font-family:'Outfit',sans-serif; color:var(--color-guindo); margin-bottom:20px;">Asignar Horario de Clase</h3>
        
        <form action="{{ route('schedules.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Docente *</label>
                <select name="user_id" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}">{{ $docente->name }} ({{ $docente->codigo_docente }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid; grid-template-columns:2fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label style="font-size:12px; font-weight:600;">Nombre Materia *</label>
                    <input type="text" name="materia" required placeholder="Ej: Arquitectura de Software" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600;">Código Materia</label>
                    <input type="text" name="codigo_materia" placeholder="SIS-401" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label style="font-size:12px; font-weight:600;">Aula / Laboratorio *</label>
                    <input type="text" name="aula" required placeholder="Aula 101" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600;">Día de la Semana *</label>
                    <select name="dia_semana" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                        <option value="LUNES">LUNES</option>
                        <option value="MARTES">MARTES</option>
                        <option value="MIERCOLES">MIÉRCOLES</option>
                        <option value="JUEVES">JUEVES</option>
                        <option value="VIERNES">VIERNES</option>
                        <option value="SABADO">SÁBADO</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:15px;">
                <div>
                    <label style="font-size:12px; font-weight:600;">Hora Inicio *</label>
                    <input type="time" name="hora_inicio" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600;">Hora Fin *</label>
                    <input type="time" name="hora_fin" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600;">Tolerancia (min)</label>
                    <input type="number" name="tolerancia_minutos" value="10" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" onclick="document.getElementById('modalSchedule').style.display='none'" style="padding:8px 16px; border-radius:6px; border:1px solid var(--border-color); background:none; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-dac">Guardar Horario</button>
            </div>
        </form>
    </div>
</div>
@endsection
