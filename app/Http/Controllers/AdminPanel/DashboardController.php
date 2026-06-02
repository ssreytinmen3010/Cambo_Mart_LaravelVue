<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminPanel\Brand;
use App\Models\AdminPanel\Category;
use App\Models\AdminPanel\Order;
use App\Models\AdminPanel\OrderItem;
use App\Models\AdminPanel\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['payment_status', 'order_status', 'from_date', 'to_date']);
        $hasDateFilter = $request->filled('from_date') || $request->filled('to_date');

        $fromDate = null;
        $toDate = null;

        if ($hasDateFilter) {
            if ($request->filled('from_date')) {
                $fromDate = Carbon::parse($request->from_date)->startOfDay();
            }
            if ($request->filled('to_date')) {
                $toDate = Carbon::parse($request->to_date)->endOfDay();
            }
        }

        $chartFrom = $fromDate
            ?? ($this->firstOrderMonth() ?? now()->subMonths(11)->startOfMonth());
        $chartTo = $toDate ?? now()->endOfDay();

        $monthKeys = collect();
        $current = $chartFrom->copy()->startOfMonth();
        $endMonth = $chartTo->copy()->startOfMonth();
        while ($current <= $endMonth) {
            $monthKeys->push($current->copy());
            $current->addMonth();
        }

        $monthlyTemplate = $monthKeys->mapWithKeys(function ($month) {
            $key = $month->format('Y-m');

            return [
                $key => [
                    'label' => $month->format('M Y'),
                    'paid_amount' => 0.0,
                    'unpaid_amount' => 0.0,
                    'order_count' => 0,
                ],
            ];
        })->all();

        $orders = $this->filteredOrderQuery($request, $fromDate, $toDate)
            ->get(['total_amount', 'payment_status', 'created_at']);

        foreach ($orders as $order) {
            $key = $order->created_at->format('Y-m');
            if (! isset($monthlyTemplate[$key])) {
                continue;
            }

            $amount = (float) $order->total_amount;
            $monthlyTemplate[$key]['order_count']++;

            if ($order->payment_status === Order::PAYMENT_PAID) {
                $monthlyTemplate[$key]['paid_amount'] += $amount;
            } elseif ($order->payment_status === Order::PAYMENT_PENDING) {
                $monthlyTemplate[$key]['unpaid_amount'] += $amount;
            }
        }

        $monthlyChart = array_values($monthlyTemplate);

        $bestSellersQuery = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.qty) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(8);

        $this->applyOrderFilters($bestSellersQuery, $request, $fromDate, $toDate);

        $bestSellers = $bestSellersQuery
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'total_qty' => (int) $row->total_qty,
            ])
            ->values()
            ->all();

        $topProductIds = collect($bestSellers)->pluck('id')->take(5)->all();
        $bestSellerMonthly = [
            'months' => $monthKeys->map(fn ($month) => $month->format('M Y'))->all(),
            'series' => [],
        ];

        if (! empty($topProductIds)) {
            $monthlySalesQuery = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereIn('order_items.product_id', $topProductIds)
                ->select(
                    'order_items.product_id',
                    DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m') as month_key"),
                    DB::raw('SUM(order_items.qty) as total_qty')
                )
                ->groupBy('order_items.product_id', 'month_key');

            $this->applyOrderFilters($monthlySalesQuery, $request, $fromDate, $toDate);

            $monthlySales = $monthlySalesQuery->get();

            $salesByProductMonth = [];
            foreach ($monthlySales as $row) {
                $salesByProductMonth[$row->product_id][$row->month_key] = (int) $row->total_qty;
            }

            $productNames = Product::query()
                ->whereIn('id', $topProductIds)
                ->pluck('name', 'id');

            foreach ($topProductIds as $productId) {
                $seriesData = $monthKeys->map(function ($month) use ($productId, $salesByProductMonth) {
                    $key = $month->format('Y-m');

                    return (int) ($salesByProductMonth[$productId][$key] ?? 0);
                })->all();

                $bestSellerMonthly['series'][] = [
                    'name' => $productNames[$productId] ?? "Product #{$productId}",
                    'data' => $seriesData,
                ];
            }
        }

        $filteredStatsQuery = $this->filteredOrderQuery($request, $fromDate, $toDate);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'products' => Product::count(),
                'categories' => Category::count(),
                'brands' => Brand::count(),
                'orders' => (clone $filteredStatsQuery)->count(),
                'total_stock' => (int) Product::sum('stock'),
                'paid_amount' => (float) (clone $filteredStatsQuery)
                    ->where('payment_status', Order::PAYMENT_PAID)
                    ->sum('total_amount'),
                'unpaid_amount' => (float) (clone $filteredStatsQuery)
                    ->where('payment_status', Order::PAYMENT_PENDING)
                    ->sum('total_amount'),
            ],
            'monthlyChart' => $monthlyChart,
            'bestSellers' => $bestSellers,
            'bestSellerMonthly' => $bestSellerMonthly,
            'filters' => $filters,
        ]);
    }

    private function filteredOrderQuery(Request $request, ?Carbon $fromDate, ?Carbon $toDate)
    {
        $query = Order::query()->from('orders');

        $this->applyOrderFilters($query, $request, $fromDate, $toDate);

        return $query;
    }

    private function firstOrderMonth(): ?Carbon
    {
        $firstOrderAt = Order::query()->min('created_at');

        return $firstOrderAt ? Carbon::parse($firstOrderAt)->startOfMonth() : null;
    }

    private function applyOrderFilters($query, Request $request, ?Carbon $fromDate, ?Carbon $toDate): void
    {
        if ($fromDate) {
            $query->where('orders.created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('orders.created_at', '<=', $toDate);
        }

        if ($request->filled('payment_status')) {
            $query->where('orders.payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('orders.order_status', $request->order_status);
        }
    }
}
