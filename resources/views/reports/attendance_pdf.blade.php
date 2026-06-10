<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencias - DAC</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #6b0c24; /* Guindo */
            padding-bottom: 10px;
        }
        .logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 5px;
        }
        .title {
            color: #6b0c24;
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .subtitle {
            color: #d4af37;
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }
        .date-range {
            font-size: 11px;
            color: #555;
            margin-top: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #6b0c24;
            color: #ffffff;
            font-weight: bold;
            padding: 7px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        td {
            padding: 6px 7px;
            border: 1px solid #ddd;
            font-size: 10.5px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .estado-puntual {
            color: #15803d;
            font-weight: bold;
        }
        .estado-tardanza {
            color: #b45309; /* Dorado Oscuro */
            font-weight: bold;
        }
        .estado-falta {
            color: #b91c1c;
            font-weight: bold;
        }
        .summary {
            background-color: #f8fafc;
            padding: 12px;
            border-radius: 5px;
            margin-top: 15px;
            border-left: 4px solid #d4af37;
        }
        .summary p {
            margin: 4px 0;
            font-size: 11.5px;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 25px;
            text-align: center;
            font-size: 9.5px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 6px;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    <div class="header">
        @if(file_exists(public_path('images/logo_thumb.png')))
            <img src="{{ public_path('images/logo_thumb.png') }}" class="logo" alt="Logo DAC">
        @elseif(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo DAC">
        @endif
        <h1 class="title">PROGRAMA DAC – CONTROL DE ASISTENCIA</h1>
        <h2 class="subtitle">Unidad Educativa Tiquipaya</h2>
        <div class="date-range">
            Reporte generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            @if(request('fecha_inicio') || request('fecha_fin'))
                | Periodo: {{ request('fecha_inicio', 'Inicio') }} al {{ request('fecha_fin', 'Fin') }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Docente</th>
                <th>Código</th>
                <th>Materia</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Modo</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total = count($attendances);
                $puntuales = 0;
                $tardanzas = 0;
            @endphp
            @foreach($attendances as $index => $asistencia)
                @php
                    if($asistencia->estado == 'PUNTUAL') $puntuales++;
                    if($asistencia->estado == 'TARDANZA') $tardanzas++;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $asistencia->user->name ?? 'Usuario' }}</strong></td>
                    <td><code>{{ $asistencia->user->codigo_docente ?? 'N/A' }}</code></td>
                    <td>{{ $asistencia->schedule->materia ?? 'General' }}</td>
                    <td>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $asistencia->hora_marcado ?? $asistencia->hora ?? '—' }}</td>
                    <td>
                        @if($asistencia->estado == 'PUNTUAL')
                            <span class="estado-puntual">PUNTUAL</span>
                        @elseif($asistencia->estado == 'TARDANZA')
                            <span class="estado-tardanza">TARDANZA (+{{ $asistencia->minutos_retraso }}m)</span>
                        @else
                            <span class="estado-falta">{{ $asistencia->estado }}</span>
                        @endif
                    </td>
                    <td>{{ $asistencia->modo_marcado }}</td>
                </tr>
            @endforeach
            @if($total == 0)
                <tr>
                    <td colspan="8" style="text-align: center;">No hay registros para mostrar.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if($total > 0)
        @php
            $porcentajePuntualidad = round(($puntuales / $total) * 100, 1);
        @endphp
        <div class="summary">
            <strong>Resumen del Periodo:</strong>
            <p>Total de Registros: {{ $total }} | Puntuales: {{ $puntuales }} | Tardanzas: {{ $tardanzas }} | Porcentaje de Puntualidad: {{ $porcentajePuntualidad }}%</p>
        </div>
    @endif

    <div class="footer">
        Sistema Biométrico DAC v2.0 - Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} - Página <span class="page-number"></span>
    </div>

</body>
</html>
