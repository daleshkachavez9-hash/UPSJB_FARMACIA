<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sale;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Reporte de ventas filtrado por periodo, vendedor y categoría.
     */
    public function sales(Request $request)
    {
        // Validación de los filtros (todos son opcionales)
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        // Si no se envían fechas se muestra el mes actual hasta hoy
        $from = isset($filters['from']) ? Carbon::parse($filters['from']) : now()->startOfMonth();
        $to = isset($filters['to']) ? Carbon::parse($filters['to']) : now();

        // Si las fechas vienen invertidas se intercambian
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $userId = $filters['user_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        // Ventas del periodo aplicando los filtros elegidos
        $sales = Sale::with(['customer', 'user'])
            ->whereBetween('date', [$from, $to])
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                // Solo ventas que incluyen al menos un medicamento de la categoría
                $query->whereHas('details.medicine', function ($medicine) use ($categoryId) {
                    $medicine->where('category_id', $categoryId);
                });
            })
            ->orderBy('date')
            ->get();

        // Tarjetas de resumen
        $totalSold = $sales->sum('total');
        $salesCount = $sales->count();
        $averageTicket = $salesCount > 0 ? $totalSold / $salesCount : 0;

        // Gráfico de barras: total vendido por cada día del periodo (los días sin ventas van en 0)
        $salesByDay = $sales->groupBy(fn ($sale) => $sale->date->format('Y-m-d'));

        $dayLabels = [];
        $dayTotals = [];
        foreach (CarbonPeriod::create($from, '1 day', $to) as $day) {
            $dayLabels[] = $day->format('d/m/Y');
            $dayTotals[] = round((float) ($salesByDay->get($day->format('Y-m-d'))?->sum('total') ?? 0), 2);
        }

        // Opciones de los selectores
        $users = User::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('principal.reports.sales', compact(
            'from', 'to', 'userId', 'categoryId',
            'sales', 'totalSold', 'salesCount', 'averageTicket',
            'dayLabels', 'dayTotals',
            'users', 'categories'
        ));
    }
}
