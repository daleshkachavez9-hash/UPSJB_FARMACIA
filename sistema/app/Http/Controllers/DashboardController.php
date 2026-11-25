<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DetailSale;
use App\Models\Medicine;
use App\Models\Sale;

class DashboardController extends Controller
{
    /**
     * Muestra el resumen de estadísticas y los gráficos del dashboard.
     */
    public function index()
    {
        // Rango del mes actual (del día 1 a fin de mes)
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // ---- Tarjetas de resumen ----
        $soldToday = Sale::whereDate('date', today())->sum('total');
        $soldMonth = Sale::whereBetween('date', [$monthStart, $monthEnd])->sum('total');
        $totalCustomers = Customer::count();
        $lowStock = Medicine::where('stock', '<=', MedicineController::LOW_STOCK)->count();
        $lowStockLimit = MedicineController::LOW_STOCK;

        // ---- Gráfico de líneas: ventas de los últimos 7 días ----
        $weekStart = today()->subDays(6);

        // Suma del total vendido agrupado por día: ['2026-11-19' => 40.50, ...]
        $salesByDay = Sale::selectRaw('DATE(date) as day, SUM(total) as total')
            ->where('date', '>=', $weekStart)
            ->groupBy('day')
            ->pluck('total', 'day');

        // Se recorren los 7 días para que los días sin ventas aparezcan con 0
        $weekLabels = [];
        $weekTotals = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $weekLabels[] = $day->format('d/m');
            $weekTotals[] = round((float) ($salesByDay[$day->toDateString()] ?? 0), 2);
        }

        // ---- Gráfico de dona: ventas del mes por categoría ----
        $categorySales = DetailSale::join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->join('categories', 'categories.id', '=', 'medicines.category_id')
            ->whereBetween('sales.date', [$monthStart, $monthEnd])
            ->selectRaw('categories.name as name, SUM(detail_sales.subtotal) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $categoryLabels = $categorySales->pluck('name');
        $categoryTotals = $categorySales->pluck('total')->map(fn ($total) => round((float) $total, 2));

        // ---- Gráfico de barras: 5 medicamentos más vendidos del mes ----
        $topMedicines = DetailSale::join('sales', 'sales.id', '=', 'detail_sales.sale_id')
            ->join('medicines', 'medicines.id', '=', 'detail_sales.medicine_id')
            ->whereBetween('sales.date', [$monthStart, $monthEnd])
            ->selectRaw('medicines.name as name, SUM(detail_sales.amount) as amount')
            ->groupBy('medicines.name')
            ->orderByDesc('amount')
            ->limit(5)
            ->get();

        $topLabels = $topMedicines->pluck('name');
        $topAmounts = $topMedicines->pluck('amount')->map(fn ($amount) => (int) $amount);

        return view('principal.dashboard.index', compact(
            'soldToday', 'soldMonth', 'totalCustomers', 'lowStock', 'lowStockLimit',
            'weekLabels', 'weekTotals',
            'categoryLabels', 'categoryTotals',
            'topLabels', 'topAmounts'
        ));
    }
}
