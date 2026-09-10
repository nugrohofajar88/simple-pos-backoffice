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
        $totalCash = $initialCapital + $otherIncomeTotal;

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
        ];
    }
}
