@extends('template.administrador')

@section('title', 'REPORTE DE VENTAS')
@section('name-section', 'Reporte de ventas')

@section('description', 'Consulta las ventas por periodo, vendedor y categoría')
@section('item-breadcrumb', 'Reportes')
@section('section-breadcrumb', 'Ventas')
@section('module-breadcrumb', 'Principal')

@push('estilos')
<style>
    /* Círculo de color detrás del ícono de cada tarjeta */
    .kpi-icon {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
    }

    /* Alto fijo para que el gráfico no se estire */
    .chart-box { position: relative; height: 340px; }
</style>
@endpush

@section('content')

@include('partials.alerts')

{{-- Filtros del reporte --}}
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary mb-3">
    <div class="card-body">
        <form action="{{ route('reports.sales') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-lg">
                <label for="from" class="form-label small">Desde</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="to" class="form-label small">Hasta</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="user_id" class="form-label small">Vendedor</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected($userId == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <label for="category_id" class="form-label small">Categoría</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 px-4">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary" title="Quitar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tarjetas de resumen --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Total vendido</div>
                    <div class="fs-4 fw-bold">S/ {{ number_format($totalSold, 2) }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center">
                    <i class="bi bi-cash-coin fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Número de ventas</div>
                    <div class="fs-4 fw-bold">{{ $salesCount }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center">
                    <i class="bi bi-receipt fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Ticket promedio</div>
                    <div class="fs-4 fw-bold">S/ {{ number_format($averageTicket, 2) }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center">
                    <i class="bi bi-bar-chart-line fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gráfico de barras: ventas por día --}}
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Ventas por día</h6>
        <div class="chart-box">
            <canvas id="dayChart"></canvas>
        </div>
    </div>
</div>

{{-- Tabla con el detalle de las ventas filtradas --}}
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h6 class="fw-bold mb-0">Detalle de ventas</h6>

            {{-- Descargas con los mismos filtros de la pantalla --}}
            @php
                $exportFilters = array_filter([
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                    'user_id' => $userId,
                    'category_id' => $categoryId,
                ]);
            @endphp
            <div class="d-flex gap-2">
                <a href="{{ route('reports.sales.pdf', $exportFilters) }}" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
                <a href="{{ route('reports.sales.excel', $exportFilters) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="small">Fecha</th>
                        <th class="small">Cliente</th>
                        <th class="small">Vendedor</th>
                        <th class="small text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->customer->name }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td class="text-end">S/ {{ number_format($sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-3">No hay ventas con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('dayChart');

        // Si la librería no está en public/js, se muestra un aviso en lugar del gráfico
        if (typeof Chart === 'undefined') {
            canvas.parentElement.innerHTML = '<p class="text-danger small mb-0">No se encontró el archivo public/js/chart.umd.min.js</p>';
            return;
        }

        // Color principal de la plantilla (Bootstrap)
        const styles = getComputedStyle(document.documentElement);
        const primary = styles.getPropertyValue('--bs-primary').trim() || '#e63946';

        // Formato de moneda para el eje y el tooltip
        const soles = (value) => 'S/ ' + Number(value).toFixed(2);

        Chart.defaults.font.family = styles.getPropertyValue('--bs-body-font-family');
        Chart.defaults.color = '#6c757d';

        // Datos enviados desde el controlador
        const dayLabels = @json($dayLabels);
        const dayTotals = @json($dayTotals);

        const chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: dayLabels,
                datasets: [{
                    label: 'Vendido',
                    data: dayTotals,
                    backgroundColor: primary,
                    borderRadius: 3,
                    maxBarThickness: 24
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => soles(ctx.parsed.y) } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: soles } }
                }
            }
        });

        // Cuando termina de cargar la fuente se redibuja el gráfico para que los textos no se corten
        document.fonts.ready.then(function () {
            chart.update();
        });
    });
</script>
@endpush
