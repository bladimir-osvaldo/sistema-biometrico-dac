@extends('layouts.app')

@section('title', 'Gestión de Justificaciones')

@section('content')
<div class="card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="fa-solid fa-file-signature"></i> Solicitudes de Justificación de Inasistencia / Tardanzas
        </div>
        <button class="btn-dac" onclick="document.getElementById('modalJustification').style.display='flex'">
            <i class="fa-solid fa-plus"></i> Nueva Justificación
        </button>
    </div>

    <div class="table-responsive">
        <table class="dac-table">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Fecha a Justificar</th>
                    <th>Motivo</th>
                    <th>Comprobante</th>
                    <th>Estado</th>
                    <th>Revisado Por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($justifications as $just)
                <tr>
                    <td>
                        <strong>{{ $just->user->name }}</strong><br>
                        <small style="color:var(--text-muted);">{{ $just->user->codigo_docente }}</small>
                    </td>
                    <td><strong>{{ $just->fecha_inasistencia->format('d/m/Y') }}</strong></td>
                    <td>
                        <strong style="color:var(--color-guindo);">{{ $just->motivo }}</strong><br>
                        <small>{{ Str::limit($just->descripcion, 50) }}</small>
                    </td>
                    <td>
                        @if($just->archivo_adjunto)
                            <a href="{{ asset('storage/' . $just->archivo_adjunto) }}" target="_blank" class="badge badge-info"><i class="fa-solid fa-paperclip"></i> Ver Adjunto</a>
                        @else
                            <small style="color:var(--text-muted);">Sin archivo</small>
                        @endif
                    </td>
                    <td>
                        @if($just->estado === 'PENDIENTE')
                            <span class="badge badge-warning">PENDIENTE</span>
                        @elseif($just->estado === 'APROBADO')
                            <span class="badge badge-success">APROBADO</span>
                        @else
                            <span class="badge badge-danger">RECHAZADO</span>
                        @endif
                    </td>
                    <td>
                        <small>{{ $just->reviewer->name ?? 'Pendiente de revisión' }}</small>
                    </td>
                    <td>
                        @if(!auth()->user()->hasRole('Docente') && $just->estado === 'PENDIENTE')
                        <button class="btn-dac-gold" style="padding:4px 8px; font-size:11px;" onclick="openReviewModal('{{ $just->id }}', '{{ $just->user->name }}', '{{ $just->motivo }}')">
                            Revisar
                        </button>
                        @else
                            <small style="color:var(--text-muted);">Revisado</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        {{ $justifications->links() }}
    </div>
</div>

<!-- MODAL SOLICITAR JUSTIFICACIÓN -->
<div id="modalJustification" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); width:100%; max-width:500px; padding:30px; border-radius:12px; box-shadow:var(--card-shadow); border-top:5px solid var(--color-guindo);">
        <h3 style="font-family:'Outfit',sans-serif; color:var(--color-guindo); margin-bottom:20px;">Solicitar Justificación de Inasistencia</h3>
        
        <form action="{{ route('justifications.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Fecha de Inasistencia / Tardanza *</label>
                <input type="date" name="fecha_inasistencia" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Motivo Resumido *</label>
                <input type="text" name="motivo" required placeholder="Ej: Licencia Médica / Paro de Transporte" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Explicación Detallada *</label>
                <textarea name="descripcion" rows="3" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);"></textarea>
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Adjuntar Comprobante (PDF, JPG, PNG)</label>
                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" onclick="document.getElementById('modalJustification').style.display='none'" style="padding:8px 16px; border-radius:6px; border:1px solid var(--border-color); background:none; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-dac">Enviar Solicitud</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL REVISAR JUSTIFICACIÓN (COORDINADOR) -->
<div id="modalReview" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); width:100%; max-width:450px; padding:30px; border-radius:12px; box-shadow:var(--card-shadow); border-top:5px solid var(--color-dorado);">
        <h3 style="font-family:'Outfit',sans-serif; color:var(--color-guindo); margin-bottom:10px;">Revisar Justificación</h3>
        <p id="reviewInfo" style="font-size:13px; color:var(--text-muted); margin-bottom:20px;"></p>
        
        <form id="formReview" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Decisión de Revisión *</label>
                <select name="estado" required style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
                    <option value="APROBADO">APROBAR JUSTIFICACIÓN</option>
                    <option value="RECHAZADO">RECHAZAR SOLICITUD</option>
                </select>
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Observación / Comentario</label>
                <textarea name="comentario_coordinador" rows="3" placeholder="Ingresa tus observaciones..." style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" onclick="document.getElementById('modalReview').style.display='none'" style="padding:8px 16px; border-radius:6px; border:1px solid var(--border-color); background:none; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-dac-gold">Guardar Dictamen</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReviewModal(id, docente, motivo) {
        document.getElementById('reviewInfo').innerHTML = 'Docente: <strong>' + docente + '</strong><br>Motivo: <em>' + motivo + '</em>';
        document.getElementById('formReview').action = '/justifications/' + id + '/review';
        document.getElementById('modalReview').style.display = 'flex';
    }
</script>
@endsection
