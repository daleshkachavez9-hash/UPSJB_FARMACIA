@extends('template.administrador')

@section('title', 'DASHBOARD')
@section('name-section', 'Dashboard')

@section('description', 'Resumen de estadísticas')
@section('item-breadcrumb', 'Inicio')
@section('section-breadcrumb', 'Dashboard')
@section('module-breadcrumb', 'Principal')

@push('estilos')
<style>
    /* Círculo de color detrás del ícono de cada tarjeta */
    .kpi-icon {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
    }

    /* Alto fijo para que los gráficos no se estiren */
    .chart-box { position: relative; height: 320px; }
    .chart-box-sm { position: relative; height: 300px; }
</style>
@endpush

@section('content')

{{-- Tarjetas de resumen --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Vendido hoy</div>
                    <div class="fs-4 fw-bold">S/ {{ number_format($soldToday, 2) }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center">
                    <i class="bi bi-cash-coin fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Vendido este mes</div>
                    <div class="fs-4 fw-bold">S/ {{ number_format($soldMonth, 2) }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center">
                    <i class="bi bi-calendar-check fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('customers.index') }}" class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100 text-decoration-none text-dark">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Clientes registrados</div>
                    <div class="fs-4 fw-bold">{{ $totalCustomers }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center">
                    <i class="bi bi-people fs-5"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="{{ route('medicines.index', ['low_stock' => 1]) }}" class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100 text-decoration-none text-dark">
            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-secondary small">Medicamentos con stock bajo</div>
                    <div class="fs-4 fw-bold">{{ $lowStock }}</div>
                    <div class="text-secondary small">Stock igual o menor a {{ $lowStockLimit }}</div>
                </div>
                <div class="kpi-icon rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center">
                    <i class="bi bi-exclamation-triangle fs-5"></i>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Gráfico de líneas: ventas de los últimos 7 días --}}
<div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Ventas de los últimos 7 días</h6>
        <div class="chart-box">
            <canvas id="weekChart"></canvas>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Gráfico de dona: ventas del mes por categoría --}}
    <div class="col-12 col-lg-5">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Ventas del mes por categoría</h6>
                @if ($categoryLabels->isEmpty())
                    <p class="text-secondary small mb-0">Aún no hay ventas registradas este mes.</p>
                @else
                    <div class="chart-box-sm">
                        <canvas id="categoryChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Gráfico de barras: 5 medicamentos más vendidos del mes --}}
    <div class="col-12 col-lg-7">
        <div class="card bg-white border-0 shadow-sm border-5 border-top border-secondary h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">5 medicamentos más vendidos del mes</h6>
                @if ($topLabels->isEmpty())
                    <p class="text-secondary small mb-0">Aún no hay ventas registradas este mes.</p>
                @else
                    <div class="chart-box-sm">
                        <canvas id="topChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Si la librería no está en public/js, se muestra un aviso en lugar de los gráficos
        if (typeof Chart === 'undefined') {
            document.querySelectorAll('canvas').forEach(function (canvas) {
                canvas.parentElement.innerHTML = '<p class="text-danger small mb-0">No se encontró el archivo public/js/chart.umd.min.js</p>';
            });
            return;
        }

        // Colores tomados de la plantilla (Bootstrap)
        const styles = getComputedStyle(document.documentElement);
        const primary = styles.getPropertyValue('--bs-primary').trim() || '#e63946';
        const palette = [primary, '#343a40', '#495867', '#e5737f', '#8d99ae', '#c1121f', '#212529', '#6c757d'];

        // Formato de moneda para ejes y tooltips
        const soles = (value) => 'S/ ' + Number(value).toFixed(2);

        Chart.defaults.font.family = styles.getPropertyValue('--bs-body-font-family');
        Chart.defaults.color = '#6c757d';

        // Datos enviados desde el controlador
        const weekLabels = @json($weekLabels);
        const weekTotals = @json($weekTotals);
        const categoryLabels = @json($categoryLabels);
        const categoryTotals = @json($categoryTotals);
        const topLabels = @json($topLabels);
        const topAmounts = @json($topAmounts);

        // Línea: ventas de los últimos 7 días
        new Chart(document.getElementById('weekChart'), {
            type: 'line',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Vendido',
                    data: weekTotals,
                    borderColor: primary,
                    backgroundColor: primary + '33',
                    pointBackgroundColor: primary,
                    fill: true,
                    tension: 0.4
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

        // Dona: ventas del mes por categoría
        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas) {
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryTotals,
                        backgroundColor: palette,
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '55%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 14 } },
                        tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + soles(ctx.parsed) } }
                    }
                }
            });
        }

        // Barras horizontales: 5 medicamentos más vendidos del mes
        const topCanvas = document.getElementById('topChart');
        if (topCanvas) {
            new Chart(topCanvas, {
                type: 'bar',
                data: {
                    labels: topLabels,
                    datasets: [{
                        label: 'Unidades vendidas',
                        data: topAmounts,
                        backgroundColor: palette.slice(0, 5),
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // Cuando termina de cargar la fuente se redibujan los gráficos para que los textos no se corten
        document.fonts.ready.then(function () {
            Object.values(Chart.instances).forEach((chart) => chart.update());
        });
    });
</script>
@endpush
