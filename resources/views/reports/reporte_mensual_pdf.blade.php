<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual de Asistencia</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1e293b; line-height: 1.4; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-bottom: 2px solid #6b0c24; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-title { color: #6b0c24; font-size: 18px; font-weight: bold; }
        .logo-sub { color: #d4af37; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 20px; }
        .info-table { width: 100%; }
        .info-table td { padding: 4px 8px; }
        .kpi-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .kpi-table td { text-align: center; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; }
        .kpi-value { font-size: 16px; font-weight: bold; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th { background-color: #1e293b; color: #ffffff; padding: 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        .table-data td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .badge { padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 10px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="logo-title">SISTEMA BIOMÉTRICO DE ASISTENCIA DAC</div>
                <div class="logo-sub">Programa DAC · Unidad Educativa Tiquipaya</div>
            </td>
            <td style="text-align: right;">
                <strong>REPORTE MENSUAL DE ASISTENCIA</strong><br>
                <span>Período: {{ $nombreMes }} / {{ $anio }}</span>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <table class="info-table">
            <tr>
                <td><strong>Docente:</strong> {{ $docente->name }}</td>
                <td><strong>CI / DNI:</strong> {{ $docente->dni ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Código Docente:</strong> {{ $docente->codigo_docente ?? 'N/A' }}</td>
                <td><strong>Cargo / Carrera:</strong> {{ $docente->cargo }} ({{ $docente->carrera ?? 'General' }})</td>
            </tr>
        </table>
    </div>

    <table class="kpi-table">
        <tr>
            <td style="background-color: #f0fdf4;">
                <div class="kpi-value" style="color: #15803d;">{{ $totalPuntuales }}</div>
                <div style="font-size: 10px; color: #166534;">Puntuales</div>
            </td>
            <td style="background-color: #fffbeb;">
                <div class="kpi-value" style="color: #b45309;">{{ $totalTardanzas }}</div>
                <div style="font-size: 10px; color: #92400e;">Tardanzas</div>
            </td>
            <td style="background-color: #fef2f2;">
                <div class="kpi-value" style="color: #b91c1c;">{{ $totalFaltas }}</div>
                <div style="font-size: 10px; color: #991b1b;">Faltas</div>
            </td>
            <td style="background-color: #eff6ff;">
                <div class="kpi-value" style="color: #1d4ed8;">{{ $totalJustificadas }}</div>
                <div style="font-size: 10px; color: #1e40af;">Justificadas</div>
            </td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora Marcado</th>
                <th>Estado</th>
                <th>Retraso</th>
                <th>Dispositivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $att)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($att->fecha)->format('d/m/Y') }}</td>
                    <td>{{ substr($att->hora_marcado, 0, 5) }}</td>
                    <td>
                        @if($att->estado == 'PUNTUAL')
                            <span class="badge badge-success">PUNTUAL</span>
                        @elseif($att->estado == 'TARDANZA')
                            <span class="badge badge-warning">TARDANZA</span>
                        @else
                            <span class="badge badge-danger">{{ $att->estado }}</span>
                        @endif
                    </td>
                    <td>{{ $att->minutos_retraso > 0 ? $att->minutos_retraso . ' min' : '-' }}</td>
                    <td>{{ $att->device->nombre ?? $att->modo_marcado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No hay registros de asistencia en este período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado el {{ $fechaEmision }} — Sistema Biométrico DAC (Unidad Educativa Tiquipaya)
    </div>
</body>
</html>
