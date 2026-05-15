<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Services\DashboardService;
use Carbon\Carbon;

/**
 * Controlador de Dashboard Principal del Sistema Biométrico DAC.
 * Proporciona KPIs reales, filtros por período/docente, datos para gráficos
 * e información de hardware en vivo, con alcance según el rol del usuario.
 */
class DashboardController extends Controller
{
    protected DashboardService $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Filtros recibidos desde la vista (mes, año y docente seleccionado)
        $mes        = $request->input('mes', date('m'));
        $anio       = $request->input('anio', date('Y'));
        $selectedId = $request->input('user_id');
        $period     = $this->service->resolvePeriod($mes, $anio);

        // Alcance según rol (Docente => solo sus datos)
        $effectiveUserId = $this->service->scopedUserId($selectedId ? (int) $selectedId : null);

        // 1. KPIs del período (mes/año) con el alcance resuelto
        $kpis = $this->service->kpis($period, $effectiveUserId);
        $totalAsistencias       = $kpis['totalAsistencias'];
        $puntuales              = $kpis['puntuales'];
        $tardanzas              = $kpis['tardanzas'];
        $faltas                 = $kpis['faltas'];
        $porcentajePuntualidad  = $kpis['porcentajePuntualidad'];

        // 2. Serie semanal para el gráfico de barras
        $chart = $this->service->weeklySeries($effectiveUserId, 7);
        $chartDias      = $chart['labels'];
        $chartPuntuales = $chart['puntuales'];
        $chartTardanzas = $chart['tardanzas'];
        $chartFaltas    = $chart['faltas'];

        // 3. Distribución del período para el gráfico de donut
        $chartDonutData = [$puntuales, $tardanzas, $faltas];

        // 4. Últimas marcaciones (con alcance por rol)
        $ultimasAsistencias = $this->service->ultimasAsistencias($effectiveUserId, 10);

        // 5. Datos operativos según rol (Admin/Coordinador)
        $esAdmin = $user->hasRole('Administrador');
        $esCoordinador = $user->hasRole('Coordinador DAC');

        $dispositivosOnline = 0;
        $totalDispositivos  = 0;
        $devices            = collect();
        $justificacionesPendientes = 0;
        $docentesConAlertas = collect();

        if ($esAdmin || $esCoordinador) {
            // Estado de dispositivos según regla de 5 minutos
            $limiteCincoMinutos = Carbon::now()->subMinutes(5);
            Device::where('ultimo_heartbeat', '<', $limiteCincoMinutos)->update(['estado' => 'OFFLINE']);
            Device::where('ultimo_heartbeat', '>=', $limiteCincoMinutos)->update(['estado' => 'ONLINE']);

            $dispositivosOnline = Device::where('estado', 'ONLINE')->count();
            $totalDispositivos  = Device::count();
            $devices            = Device::latest()->get();

            $justificacionesPendientes = $this->service->justificacionesPendientes();
            $docentesConAlertas        = $this->service->docentesConAlertas($period);
        }

        // Datos para el selector de período y búsqueda de docente
        $docentes = $esAdmin || $esCoordinador ? $this->service->docentes() : collect();

        return view('dashboard', compact(
            'totalAsistencias',
            'puntuales',
            'tardanzas',
            'faltas',
            'porcentajePuntualidad',
            'chartDonutData',
            'chartDias',
            'chartPuntuales',
            'chartTardanzas',
            'chartFaltas',
            'ultimasAsistencias',
            'dispositivosOnline',
            'totalDispositivos',
            'devices',
            'justificacionesPendientes',
            'docentesConAlertas',
            'docentes',
            'mes',
            'anio',
            'period',
            'effectiveUserId',
            'esAdmin',
            'esCoordinador'
        ));
    }
}
