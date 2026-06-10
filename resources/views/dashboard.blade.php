@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $startFecha = \Carbon\Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString();
    $endFecha   = \Carbon\Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString();
    $filtros    = ['mes' => $mes, 'anio' => $anio];
    if ($effectiveUserId) { $filtros['user_id'] = $effectiveUserId; }
    $filtrosURL = $filtros + ['fecha_inicio' => $startFecha, 'fecha_fin' => $endFecha];
@endphp

{{-- Alertas Inteligentes (Admin / Coordinador) --}}
@if(isset($docentesConAlertas) && count($docentesConAlertas) > 0)
    <div class="alert alert-warning alert-custom mb-4">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-bell fa-lg text-warning me-2"></i>
                <div>
                    <strong>Alerta de Impuntualidad:</strong> Hay <strong>{{ count($docentesConAlertas) }} docentes</strong> con 3 o más tardanzas en {{ $period }}.
                    <div class="small text-muted mt-1">
                        @foreach($docentesConAlertas as $doc)
                            <span class="badge badge-warning me-1">{{ $doc->name }} ({{ $doc->tardanzas_mes_count }} tardanzas)</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @can('ver memorandos')
                <a href="{{ route('memorandos.index') }}" class="btn btn-sm btn-dac-gold text-nowrap">Gestionar Memorandos</a>
            @endcan
        </div>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="page-title mb-0"><i class="fa-solid fa-chart-pie text-guindo me-2"></i>Centro de Control Biométrico</h2>
        <p class="text-muted small mb-0">Resumen operativo en tiempo real — Programa DAC Tiquipaya</p>
    </div>
    <div class="text-muted small">
        <i class="fas fa-clock me-1"></i> Actualizado: {{ date('d/m/Y H:i') }}
    </div>
</div>

{{-- ─── BARRA DE FILTROS ─── --}}
<form method="GET" action="{{ route('dashboard') }}" id="filtersForm" class="card mb-4 p-3">
    <div class="row g-3 align-items-end">
        <div class="col-auto">
            <label class="form-label mb-1" for="mes">Mes</label>
            <select name="mes" id="mes" class="form-select" style="min-width:130px;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ (int)$mes === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label mb-1" for="anio">Año</label>
            <select name="anio" id="anio" class="form-select" style="min-width:110px;">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ (int)$anio === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        {{-- Búsqueda de docente con autocompletar (solo Admin / Coordinador) --}}
        @if($esAdmin || $esCoordinador)
        <div class="col">
            <label class="form-label mb-1" for="docenteSearch">Docente</label>
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px;"></i>
                <input type="text"
                       id="docenteSearch"
                       class="form-control"
                       style="padding-left:38px;"
                       placeholder="Buscar docente por nombre, CI o código..."
                       autocomplete="off"
                       value="{{ $docentes->firstWhere('id', $effectiveUserId)->name ?? '' }}">
                <input type="hidden" name="user_id" id="user_id" value="{{ $effectiveUserId ?? '' }}">
                @if($effectiveUserId)
                    <button type="button" id="clearDocente" title="Mostrar todos" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                @endif
            </div>
            <div id="docenteResults" style="display:none; position:absolute; z-index:400; background:var(--bg-card); border:1px solid var(--border-color); border-radius:10px; box-shadow:var(--card-shadow); max-height:260px; overflow-y:auto; width:100%;"></div>
        </div>
        @endif

        <div class="col-auto">
            <button type="submit" class="btn-dac"><i class="fa-solid fa-filter"></i> Aplicar Filtro</button>
            <a href="{{ route('dashboard') }}" class="btn-dac-outline ms-1">Limpiar</a>
        </div>
    </div>
</form>

{{-- ─── TARJETAS KPI (clicables → vista detallada) ─── --}}
<div class="stats-grid mb-4">
    <a href="{{ route('attendances.index', $filtrosURL) }}" class="stat-card text-decoration-none" style="color:inherit;">
        <div class="stat-icon guindo"><i class="fas fa-clipboard-check"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $totalAsistencias }}</div>
            <div class="stat-label">Total Asistencias</div>
            <div class="stat-trend up"><i class="fas fa-check"></i> {{ $puntuales }} puntuales</div>
        </div>
    </a>

    <a href="{{ route('attendances.index', $filtrosURL + ['estado' => 'TARDANZA']) }}" class="stat-card text-decoration-none" style="color:inherit;">
        <div class="stat-icon dorado"><i class="fas fa-stopwatch"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $tardanzas }}</div>
            <div class="stat-label">Total Tardanzas</div>
            <div class="stat-trend down"><i class="fas fa-arrow-up"></i> ver detalle</div>
        </div>
    </a>

    <a href="{{ route('attendances.index', $filtrosURL + ['estados' => ['FALTA_INJUSTIFICADA','FALTA_JUSTIFICADA']]) }}" class="stat-card text-decoration-none" style="color:inherit;">
        <div class="stat-icon rojo"><i class="fas fa-user-xmark"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $faltas }}</div>
            <div class="stat-label">Total Faltas</div>
            <div class="stat-trend down"><i class="fas fa-arrow-up"></i> ver detalle</div>
        </div>
    </a>

    <a href="{{ route('attendances.index', $filtrosURL) }}" class="stat-card text-decoration-none" style="color:inherit;">
        <div class="stat-icon azul"><i class="fas fa-percentage"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $porcentajePuntualidad }}%</div>
            <div class="stat-label">Puntualidad</div>
            <div class="stat-trend {{ $porcentajePuntualidad >= 80 ? 'up' : 'down' }}">
                <i class="fas fa-chart-line"></i> del período
            </div>
        </div>
    </a>

    @if($esAdmin || $esCoordinador)
    <div class="stat-card">
        <div class="stat-icon plomo"><i class="fas fa-microchip"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $dispositivosOnline }} / {{ $totalDispositivos }}</div>
            <div class="stat-label">ESP32 En Línea</div>
            <div class="stat-trend up"><i class="fas fa-signal"></i> Cobertura activa</div>
        </div>
    </div>
    @endif
</div>

<div class="row">
    {{-- COLUMNA PRINCIPAL --}}
    <div class="col-lg-8 mb-4">
        <div class="card mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title-custom mb-0"><i class="fas fa-chart-bar me-2"></i>Evolución de Asistencias (7 días)</h5>
                <span class="badge badge-guindo">Período: {{ $period }}</span>
            </div>
            <div class="card-body">
                <div style="height: 260px;">
                    <canvas id="barAsistenciasChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title-custom mb-0"><i class="fas fa-list me-2"></i>Últimas Marcaciones Biométricas</h5>
                <a href="{{ route('attendances.index', $filtrosURL) }}" class="btn btn-sm btn-dac-outline">Ver Todo</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dac-table mb-0">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th>Materia / Dispositivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasAsistencias as $att)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $att->user->foto_url ?? 'https://ui-avatars.com/api/?name='.urlencode($att->user->name ?? 'D') }}" class="user-avatar-sm" alt="Avatar">
                                            <div>
                                                <strong>{{ $att->user->name ?? 'Docente' }}</strong>
                                                <div class="small text-muted">{{ $att->user->codigo_docente ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>{{ substr($att->hora_marcado, 0, 5) }}</strong><br><small class="text-muted">{{ \Carbon\Carbon::parse($att->fecha)->format('d/m') }}</small></td>
                                    <td>
                                        @if($att->estado == 'PUNTUAL')
                                            <span class="badge badge-success">Puntual</span>
                                        @elseif($att->estado == 'TARDANZA')
                                            <span class="badge badge-warning">Tardanza (+{{ $att->minutos_retraso }}m)</span>
                                        @else
                                            <span class="badge badge-danger">Falta</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $att->schedule->materia ?? 'Clase' }}</small><br>
                                        <small class="text-muted"><i class="fas fa-microchip me-1"></i>{{ $att->device->nombre ?? $att->modo_marcado }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay marcaciones registradas en el período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PANEL LATERAL --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header-custom">
                <h5 class="card-title-custom mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución del Período</h5>
            </div>
            <div class="card-body">
                <div style="height: 220px; position: relative;">
                    <canvas id="donutDistribucionChart"></canvas>
                </div>
            </div>
        </div>

        @if($esAdmin || $esCoordinador)
        <div class="card">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="card-title-custom mb-0"><i class="fas fa-tower-broadcast me-2"></i>Lectores ESP32</h5>
                <a href="{{ route('monitoreo.index') }}" class="btn btn-sm btn-dac-outline">Monitor</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($devices as $dev)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
                            <div>
                                <strong class="d-block">{{ $dev->nombre }}</strong>
                                <small class="text-muted">{{ $dev->ubicacion_aula }} ({{ $dev->codigo }})</small>
                                @if($dev->ultimo_heartbeat)
                                    <div class="text-muted" style="font-size: 10px;">
                                        Hace {{ $dev->ultimo_heartbeat->diffForHumans() }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                @if($dev->estado == 'ONLINE')
                                    <span class="badge badge-success"><i class="fas fa-circle text-success me-1"></i>En Línea</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-circle text-danger me-1"></i>Fuera de Línea</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted">No hay dispositivos configurados.</div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Estado actual de los filtros
    const filtersForm = document.getElementById('filtersForm');
    const mesSelect   = document.getElementById('mes');
    const anioSelect  = document.getElementById('anio');

    // ─── 1. Gráfico de Donut (distribución del período) ───
    const donutCtx = document.getElementById('donutDistribucionChart').getContext('2d');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Puntual', 'Tardanza', 'Falta'],
            datasets: [{
                data: @json($chartDonutData),
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // ─── 2. Gráfico de Barras (serie dinámica vía API) ───
    const barCtx = document.getElementById('barAsistenciasChart').getContext('2d');
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: @json($chartDias),
            datasets: [
                { label: 'Puntuales', data: @json($chartPuntuales), backgroundColor: '#22c55e' },
                { label: 'Tardanzas', data: @json($chartTardanzas), backgroundColor: '#f59e0b' },
                { label: 'Faltas', data: @json($chartFaltas), backgroundColor: '#ef4444' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            },
            plugins: { legend: { position: 'top' } }
        }
    });

    // ─── 3. Refrescar el gráfico de barras desde la API ───
    function refreshBarChart() {
        const userId = document.getElementById('user_id')?.value || '';
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - 6);

        const params = new URLSearchParams({ start: start.toISOString().slice(0,10), end: end.toISOString().slice(0,10) });
        if (userId) params.set('user_id', userId);

        fetch('{{ route("api.reports.attendance") }}?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                barChart.data.labels = data.labels;
                barChart.data.datasets[0].data = data.puntuales;
                barChart.data.datasets[1].data = data.tardanzas;
                barChart.data.datasets[2].data = data.faltas;
                barChart.update();
            })
            .catch(() => {});
    }

    // ─── 4. Búsqueda de docente con autocompletar (Admin/Coordinador) ───
    const searchInput    = document.getElementById('docenteSearch');
    const hiddenUserId   = document.getElementById('user_id');
    const resultsBox     = document.getElementById('docenteResults');
    const clearBtn       = document.getElementById('clearDocente');

    let debounceTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const q = this.value.trim();

            if (q.length < 2) {
                resultsBox.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch('{{ route("api.search.docentes") }}?q=' + encodeURIComponent(q))
                    .then(r => {
                        if (r.status === 403) { throw new Error('forbidden'); }
                        return r.json();
                    })
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (!data.docentes || data.docentes.length === 0) {
                            resultsBox.innerHTML = '<div class="p-3 text-muted small">Sin resultados para «' + q + '»</div>';
                        } else {
                            data.docentes.forEach(d => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2';
                                item.style.cssText = 'width:100%; border:none; background:none; text-align:left; cursor:pointer;';
                                item.innerHTML =
                                    '<div><strong style="font-size:13px;">' + d.name + '</strong>' +
                                    '<div class="small text-muted">' + (d.codigo_docente || 'Sin código') + ' · CI: ' + (d.dni || 'N/A') + '</div></div>';
                                item.addEventListener('click', () => {
                                    hiddenUserId.value = d.id;
                                    searchInput.value  = d.name;
                                    resultsBox.style.display = 'none';
                                    filtersForm.submit();
                                });
                                resultsBox.appendChild(item);
                            });
                        }
                        resultsBox.style.display = 'block';
                    })
                    .catch(() => { resultsBox.style.display = 'none'; });
            }, 300);
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#docenteSearch') && !e.target.closest('#docenteResults')) {
                resultsBox.style.display = 'none';
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            hiddenUserId.value = '';
            searchInput.value = '';
            filtersForm.submit();
        });
    }

    // Aplicar filtro de mes/año y refrescar gráficos
    mesSelect.addEventListener('change', () => filtersForm.submit());
    anioSelect.addEventListener('change', () => filtersForm.submit());

    refreshBarChart();
});
</script>
@endsection
