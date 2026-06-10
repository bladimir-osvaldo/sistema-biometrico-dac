<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alerta de Tardanzas</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 5px solid #6b0c24; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 24px; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
        .header h2 { color: #6b0c24; margin: 0; }
        .content { padding: 20px 0; }
        .badge { background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 4px; font-weight: bold; }
        .footer { font-size: 12px; color: #64748b; text-align: center; padding-top: 16px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2>SISTEMA BIOMÉTRICO DAC</h2>
            <p style="color: #d4af37; margin: 4px 0 0; font-weight: bold;">Unidad Educativa Tiquipaya</p>
        </div>
        <div class="content">
            <h3>Notificación de Alerta de Impuntualidad</h3>
            <p>Estimado Coordinador Academicom,</p>
            <p>Se informa que el/la docente <strong>{{ $docente->name }}</strong> (Código: {{ $docente->codigo_docente ?? 'N/A' }}) ha acumulado un total de <span class="badge">{{ $cantidadTardanzas }} tardanzas</span> en el período <strong>{{ $mes }}</strong>.</p>
            <p>De acuerdo con la normativa del Programa DAC, le sugerimos revisar el historial de asistencias e iniciar el proceso de recomendación o emisión de memorando según corresponda.</p>
        </div>
        <div class="footer">
            Este es un correo automático generado por el Sistema Biométrico DAC. No responda a este mensaje.
        </div>
    </div>
</body>
</html>
