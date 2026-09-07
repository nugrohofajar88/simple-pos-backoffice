<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\Order;
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

        $monthTotal = (int) Order::query()
            ->where('status', 'completed')
            ->where('mobile_created_at', '>=', $startOfMonth)
            ->sum('total');

        $expenseMonthTotal = (int) Expense::query()
            ->where('mobile_created_at', '>=', $startOfMonth)
            ->orWhere(function ($query) use ($startOfMonth) {
                $query->whereNull('mobile_created_at')->where('created_at', '>=', $startOfMonth);
            })
            ->sum('amount');

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
            'monthTotal' => $monthTotal,
            'expenseMonthTotal' => $expenseMonthTotal,
            'last7Days' => $last7Days,
        ];
    }
}
