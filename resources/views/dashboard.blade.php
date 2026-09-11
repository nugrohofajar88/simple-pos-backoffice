@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-on-surface-variant mb-space-xs">
                <span class="font-label-md text-label-md uppercase tracking-wider text-outline">Omzet Hari Ini</span>
                <span class="material-symbols-outlined text-secondary text-title-lg">point_of_sale</span>
            </div>
            <div class="font-metric-display text-metric-display text-primary tracking-tight">
                Rp{{ number_format($summary['todayTotal'], 0, ',', '.') }}
                <span class="font-body-sm text-body-sm font-normal text-on-surface-variant">({{ number_format($summary['todayCups'], 0, ',', '.') }} cups)</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-on-surface-variant mb-space-xs">
                <span class="font-label-md text-label-md uppercase tracking-wider text-outline">Omzet Bulan Ini</span>
                <span class="material-symbols-outlined text-secondary text-title-lg">calendar_month</span>
            </div>
            <div class="font-metric-display text-metric-display text-primary tracking-tight">
                Rp{{ number_format($summary['monthTotal'], 0, ',', '.') }}
                <span class="font-body-sm text-body-sm font-normal text-on-surface-variant">({{ number_format($summary['monthCups'], 0, ',', '.') }} cups)</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-on-surface-variant mb-space-xs">
                <span class="font-label-md text-label-md uppercase tracking-wider text-outline">Belanja Bulan Ini</span>
                <span class="material-symbols-outlined text-secondary text-title-lg">shopping_cart</span>
            </div>
            <div class="font-metric-display text-metric-display text-primary tracking-tight">Rp{{ number_format($summary['expenseMonthTotal'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-on-surface-variant mb-space-xs">
                <span class="font-label-md text-label-md uppercase tracking-wider text-outline">Modal Awal</span>
                <span class="material-symbols-outlined text-secondary text-title-lg">savings</span>
            </div>
            <div class="font-metric-display text-metric-display text-primary tracking-tight">Rp{{ number_format($summary['initialCapital'], 0, ',', '.') }}</div>
        </div>
        <a href="{{ route('other-incomes.index') }}" class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-on-surface-variant mb-space-xs">
                <span class="font-label-md text-label-md uppercase tracking-wider text-outline">Pendapatan Lain (Total)</span>
                <span class="material-symbols-outlined text-secondary text-title-lg">payments</span>
            </div>
            <div class="font-metric-display text-metric-display text-primary tracking-tight">Rp{{ number_format($summary['otherIncomeTotal'], 0, ',', '.') }}</div>
        </a>
        <div class="bg-primary text-on-primary rounded-xl p-space-md shadow-sm">
            <div class="flex items-center justify-between text-primary-fixed-dim mb-space-xs">
                <span class="font-label-md text-label-md uppercase tracking-wider">Total Cash (Modal + Pendapatan Lain)</span>
                <span class="material-symbols-outlined text-secondary-fixed-dim text-title-lg">account_balance_wallet</span>
            </div>
            <div class="font-metric-display text-metric-display">Rp{{ number_format($summary['totalCash'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-secondary text-title-lg">bar_chart</span>
            <span class="font-title-md text-title-md font-semibold text-on-surface">Omzet 7 Hari Terakhir</span>
        </div>
        @php $max = max(max(array_column($summary['last7Days'], 'total') ?: [0]), 1); @endphp
        <div class="flex items-end justify-between gap-2" style="height: 160px;">
            @foreach ($summary['last7Days'] as $day)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <div class="font-body-sm text-[10px] text-on-surface-variant mb-1">
                        @if($day['total'] > 0){{ number_format($day['total'] / 1000, 0, ',', '.') }}@endif
                    </div>
                    <div class="w-full max-w-[28px] bg-primary rounded-t-lg"
                         style="height: {{ max(4, ($day['total'] / $max) * 120) }}px;"></div>
                    <div class="font-body-sm text-[11px] text-outline mt-1">{{ $day['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="font-body-sm text-[11px] text-outline mt-3 text-center">Angka dalam ribuan rupiah</div>
    </div>
@endsection
