<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Tardanzas</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1e293b; line-height: 1.4; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-bottom: 2px solid #6b0c24; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-title { color: #6b0c24; font-size: 18px; font-weight: bold; }
        .logo-sub { color: #d4af37; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .info-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; padding: 12px; margin-bottom: 20px; color: #92400e; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th { background-color: #6b0c24; color: #ffffff; padding: 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        .table-data td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .badge-warning { background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; }
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
                <strong>INFORME DE IMPUNTUALIDADES Y TARDANZAS</strong><br>
                <span>Período: {{ $nombreMes }} / {{ $anio }}</span>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <strong>Docente:</strong> {{ $docente->name }} | <strong>CI:</strong> {{ $docente->dni ?? 'N/A' }} | <strong>Código:</strong> {{ $docente->codigo_docente ?? 'N/A' }}<br>
        <strong>Total Impuntualidades:</strong> {{ count($tardanzas) }} eventos | <strong>Minutos Acumulados:</strong> {{ $totalMinutos }} minutos
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora Entrada</th>
                <th>Minutos de Retraso</th>
                <th>Materia / Aula</th>
                <th>Dispositivo Registrador</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tardanzas as $t)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}</td>
                    <td>{{ substr($t->hora_marcado, 0, 5) }}</td>
                    <td><span class="badge-warning">+{{ $t->minutos_retraso }} min</span></td>
                    <td>{{ $t->schedule->materia ?? 'Clase' }} ({{ $t->schedule->aula ?? 'N/A' }})</td>
                    <td>{{ $t->device->nombre ?? $t->modo_marcado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #15803d; padding: 15px;">
                        ¡Excelente! El docente no registra tardanzas en este período.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado el {{ $fechaEmision }} — Sistema Biométrico DAC (Unidad Educativa Tiquipaya)
    </div>
</body>
</html>
