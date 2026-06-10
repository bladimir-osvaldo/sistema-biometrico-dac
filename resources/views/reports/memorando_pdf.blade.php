<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Memorando Institucional</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; line-height: 1.6; margin: 0; padding: 40px; }
        .header { text-align: center; border-bottom: 3px double #6b0c24; padding-bottom: 12px; margin-bottom: 30px; }
        .header h1 { color: #6b0c24; font-size: 22px; margin: 0; font-family: 'Arial', sans-serif; text-transform: uppercase; }
        .header h3 { color: #d4af37; font-size: 14px; margin: 5px 0 0; font-family: 'Arial', sans-serif; }
        .memo-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 30px; text-decoration: underline; }
        .meta-table { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .meta-table td { padding: 6px 0; font-size: 14px; }
        .table-incidencias { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-incidencias th, .table-incidencias td { border: 1px solid #333; padding: 8px; font-size: 12px; text-align: left; }
        .table-incidencias th { background-color: #f1f5f9; }
        .legal-box { background: #f8fafc; border-left: 4px solid #6b0c24; padding: 12px; margin: 25px 0; font-style: italic; font-size: 12px; }
        .signatures { margin-top: 80px; width: 100%; }
        .signature-box { text-align: center; width: 45%; float: left; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <h1>UNIDAD EDUCATIVA TIQUIPAYA</h1>
        <h3>PROGRAMA DE ACREDITACIÓN Y CONTROL (DAC)</h3>
    </div>

    <div class="memo-title">MEMORANDO N° {{ $codigo_memo }}</div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>A:</strong></td>
            <td>Prof(a). {{ $docente->name }} (CI: {{ $docente->dni ?? 'N/A' }})</td>
        </tr>
        <tr>
            <td><strong>DE:</strong></td>
            <td>{{ $coordinador }}</td>
        </tr>
        <tr>
            <td><strong>FECHA:</strong></td>
            <td>{{ \Carbon\Carbon::parse($fecha_emision)->format('d de F de Y') }}</td>
        </tr>
        <tr>
            <td><strong>ASUNTO:</strong></td>
            <td>
                @if($tipo == 'RETRASO_REITERADO')
                    Llamada de atención por acumulación de tardanzas.
                @elseif($tipo == 'INASISTENCIA_INJUSTIFICADA')
                    Llamada de atención por falta injustificada.
                @else
                    Incumplimiento de horario de trabajo asignado.
                @endif
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #ccc; margin-bottom: 25px;">

    <p>Por medio del presente documento, la Dirección del Programa DAC y la Coordinación Académica se dirigen a su persona para poner en su conocimiento el reporte emitido por el <strong>Sistema Biométrico de Control de Asistencia</strong>, correspondiente al período comprendido entre el <strong>{{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }}</strong> y el <strong>{{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}</strong>.</p>

    <p>Se han registrado los siguientes eventos de incumplimiento en su registro biométrico de huella dactilar:</p>

    <table class="table-incidencias">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora Marcado</th>
                <th>Estado</th>
                <th>Detalle del Evento</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidencias as $inc)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($inc->fecha)->format('d/m/Y') }}</td>
                    <td>{{ substr($inc->hora_marcado, 0, 5) }}</td>
                    <td><strong>{{ $inc->estado }}</strong></td>
                    <td>
                        @if($inc->estado == 'TARDANZA')
                            Retraso de {{ $inc->minutos_retraso }} minutos respecto al horario oficial.
                        @else
                            Falta injustificada a período de clase.
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Incidencias registradas según reporte institucional.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="legal-box">
        <strong>Normativa Institucional aplicable:</strong> De acuerdo con el Reglamento Interno de Trabajo del Programa DAC, la acumulación de tardanzas o inasistencias sin justificación previa dentro de las 48 horas posteriores constituye una falta a la disciplina académica que da lugar al presente memorando con copia a su archivo personal.
    </div>

    <p>Se le insta a tomar las previsiones necesarias para corregir esta situación y dar estricto cumplimiento a los horarios establecidos.</p>

    <div class="signatures">
        <div class="signature-box" style="float: left;">
            ___________________________________<br>
            <strong>{{ $coordinador }}</strong><br>
            Coordinación Académica DAC
        </div>
        <div class="signature-box" style="float: right;">
            ___________________________________<br>
            <strong>Prof(a). {{ $docente->name }}</strong><br>
            Recibido / Firma de Conformidad
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
