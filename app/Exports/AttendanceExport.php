<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Attendance::with(['user', 'schedule', 'device']);

        if ($this->request->filled('user_id')) {
            $query->where('user_id', $this->request->user_id);
        }

        if ($this->request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $this->request->fecha_inicio);
        }

        if ($this->request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $this->request->fecha_fin);
        }

        if ($this->request->filled('estado')) {
            $query->where('estado', $this->request->estado);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Docente',
            'Código',
            'Materia',
            'Aula',
            'Fecha',
            'Hora Marcado',
            'Estado',
            'Modo',
            'Min. Retraso',
            'Observaciones'
        ];
    }

    public function map($attendance): array
    {
        $retraso = 0;
        if ($attendance->estado === 'TARDANZA' && $attendance->schedule) {
            $horaEntrada = Carbon::parse($attendance->schedule->hora_entrada);
            $horaMarcado = Carbon::parse($attendance->hora);
            $retraso = $horaEntrada->diffInMinutes($horaMarcado, false);
            if ($retraso < 0) $retraso = 0;
        }

        return [
            $attendance->user->nombre . ' ' . $attendance->user->apellido,
            $attendance->user->codigo_rfid ?? 'N/A',
            $attendance->schedule->materia ?? 'N/A',
            $attendance->schedule->aula ?? 'N/A',
            Carbon::parse($attendance->fecha)->format('d/m/Y'),
            Carbon::parse($attendance->hora)->format('H:i:s'),
            $attendance->estado,
            $attendance->modo_marcado,
            $retraso,
            $attendance->observaciones ?? ''
        ];
    }
}
