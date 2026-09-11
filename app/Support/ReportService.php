<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OtherIncome;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class ReportService
{
    public function summary(): array
    {
        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();
        $startOfMonth = $now->copy()->startOfMonth();

        $todayTotal = (int) Order::query()
            ->where('status', 'completed')
            ->where('mobile_created_at', '>=', $startOfDay)
            ->sum('total');

        $todayCups = (int) OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('status', 'completed')->where('mobile_created_at', '>=', $startOfDay))
            ->sum('qty');

        $monthTotal = (int) Order::query()
            ->where('status', 'completed')
            ->where('mobile_created_at', '>=', $startOfMonth)
            ->sum('total');

        $monthCups = (int) OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('status', 'completed')->where('mobile_created_at', '>=', $startOfMonth))
            ->sum('qty');

        $expenseMonthTotal = (int) Expense::query()
            ->where('mobile_created_at', '>=', $startOfMonth)
            ->orWhere(function ($query) use ($startOfMonth) {
                $query->whereNull('mobile_created_at')->where('created_at', '>=', $startOfMonth);
            })
            ->sum('amount');

        $initialCapital = (int) Setting::getValue('initial_capital', 0);
        $otherIncomeTotal = (int) OtherIncome::query()->sum('amount');
        $expenseTotal = (int) Expense::query()->sum('amount');
        $totalCash = $initialCapital + $otherIncomeTotal - $expenseTotal;

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $last7Days[] = [
                'dateKey' => $day->format('Y-m-d'),
                'label' => $day->translatedFormat('D'),
                'total' => (int) Order::query()
                    ->where('status', 'completed')
                    ->whereDate('mobile_created_at', $day->toDateString())
                    ->sum('total'),
            ];
        }

        return [
            'todayTotal' => $todayTotal,
            'todayCups' => $todayCups,
            'monthTotal' => $monthTotal,
            'monthCups' => $monthCups,
            'expenseMonthTotal' => $expenseMonthTotal,
            'initialCapital' => $initialCapital,
            'otherIncomeTotal' => $otherIncomeTotal,
            'totalCash' => $totalCash,
            'last7Days' => $last7Days,
            'topProducts' => $this->topProducts($startOfMonth),
        ];
    }

    private function topProducts(Carbon $since): array
    {
        $rows = OrderItem::query()
            ->select('product_name')
            ->selectRaw('SUM(qty) as total_qty')
            ->selectRaw('SUM(unit_price * qty) as total_revenue')
            ->selectRaw('SUM(cost_price * qty) as total_cost')
            ->whereHas('order', fn ($q) => $q->where('status', 'completed')->where('mobile_created_at', '>=', $since))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return $rows->map(function ($row) {
            $revenue = (int) $row->total_revenue;
            $cost = (int) $row->total_cost;

            return [
                'name' => $row->product_name,
                'qty' => (int) $row->total_qty,
                'revenue' => $revenue,
                'marginPercent' => $revenue > 0 ? (int) round((($revenue - $cost) / $revenue) * 100) : 0,
            ];
        })->all();
    }
}
