<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas</title>
    <style>
        @page { margin: 110px 35px 60px 35px; }

        /* DejaVu Sans viene con DomPDF y muestra bien las tildes y la ñ */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #323232; }

        /* Encabezado y pie fijos: se repiten en cada página */
        header { position: fixed; top: -85px; left: 0; right: 0; height: 70px; border-bottom: 3px solid #ff0a41; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 25px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 9px; padding-top: 5px; }
        footer .page:after { content: "Página " counter(page); }

        .brand { font-size: 18px; font-weight: bold; color: #ff0a41; }
        .muted { color: #6c757d; }
        .text-end { text-align: right; }

        h1 { font-size: 14px; margin: 0 0 8px 0; }

        table { width: 100%; border-collapse: collapse; }
        .filters td { padding: 2px 0; }

        .summary { margin: 12px 0; }
        .summary td { width: 33.33%; padding: 8px; background: #f8f9fa; border: 1px solid #dee2e6; }
        .summary .value { font-size: 13px; font-weight: bold; }

        .detail th { background: #ff0a41; color: #fff; padding: 6px 5px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .detail td { padding: 5px; border-bottom: 1px solid #dee2e6; }
        .detail tbody tr:nth-child(even) td { background: #f8f9fa; }
        .detail tfoot td { font-weight: bold; border-top: 2px solid #323232; border-bottom: 0; }
    </style>
</head>
<body>

<header>
    <table>
        <tr>
            <td>
                <div class="brand">FARM SJB</div>
                <div class="muted">Sistema de Venta de Medicamentos</div>
            </td>
            <td class="text-end muted">
                Generado: {{ now()->format('d/m/Y H:i') }}<br>
                Por: {{ auth()->user()->name }}
            </td>
        </tr>
    </table>
</header>

<footer>
    <table>
        <tr>
            <td>Farmacia San Juan Bautista · Reporte de ventas</td>
            <td class="text-end"><span class="page"></span></td>
        </tr>
    </table>
</footer>

<main>
    <h1>Reporte de ventas</h1>

    {{-- Filtros con los que se generó el reporte --}}
    <table class="filters">
        <tr>
            <td><strong>Periodo:</strong> {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</td>
            <td><strong>Vendedor:</strong> {{ $seller ?? 'Todos' }}</td>
            <td><strong>Categoría:</strong> {{ $category ?? 'Todas' }}</td>
        </tr>
    </table>

    {{-- Resumen --}}
    <table class="summary">
        <tr>
            <td>
                <div class="muted">Total vendido</div>
                <div class="value">S/ {{ number_format($totalSold, 2) }}</div>
            </td>
            <td>
                <div class="muted">Número de ventas</div>
                <div class="value">{{ $sales->count() }}</div>
            </td>
            <td>
                <div class="muted">Ticket promedio</div>
                <div class="value">S/ {{ number_format($sales->count() > 0 ? $totalSold / $sales->count() : 0, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Detalle de las ventas --}}
    <table class="detail">
        <thead>
            <tr>
                <th>N.°</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th class="text-end">IGV</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="text-end">S/ {{ number_format($sale->igv, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($sale->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted" style="text-align: center;">No hay ventas con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($sales->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">TOTALES</td>
                    <td class="text-end">S/ {{ number_format($totalIgv, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($totalSold, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</main>

</body>
</html>
