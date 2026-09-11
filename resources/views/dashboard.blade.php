@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4">
            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1">Omzet Hari Ini</div>
            <div class="font-metric-display text-metric-display text-on-surface">
                Rp{{ number_format($summary['todayTotal'], 0, ',', '.') }}
                <span class="font-body-sm text-body-sm font-normal text-on-surface-variant">({{ number_format($summary['todayCups'], 0, ',', '.') }} cups)</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4">
            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1">Omzet Bulan Ini</div>
            <div class="font-metric-display text-metric-display text-on-surface">
                Rp{{ number_format($summary['monthTotal'], 0, ',', '.') }}
                <span class="font-body-sm text-body-sm font-normal text-on-surface-variant">({{ number_format($summary['monthCups'], 0, ',', '.') }} cups)</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4">
            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1">Belanja Bulan Ini</div>
            <div class="font-metric-display text-metric-display text-on-surface">Rp{{ number_format($summary['expenseMonthTotal'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4">
            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1">Modal Awal</div>
            <div class="font-metric-display text-metric-display text-on-surface">Rp{{ number_format($summary['initialCapital'], 0, ',', '.') }}</div>
        </div>
        <a href="{{ route('other-incomes.index') }}" class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 hover:bg-surface-container-low">
            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1">Pendapatan Lain (Total)</div>
            <div class="font-metric-display text-metric-display text-on-surface">Rp{{ number_format($summary['otherIncomeTotal'], 0, ',', '.') }}</div>
        </a>
        <div class="bg-primary text-on-primary rounded-xl border border-primary p-4">
            <div class="font-label-sm text-label-sm text-primary-fixed-dim mb-1">Total Cash (Modal + Pendapatan Lain)</div>
            <div class="font-metric-display text-metric-display">Rp{{ number_format($summary['totalCash'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4">
        <div class="font-title-md text-title-md font-semibold text-on-surface mb-4">Omzet 7 Hari Terakhir</div>
        @php $max = max(max(array_column($summary['last7Days'], 'total') ?: [0]), 1); @endphp
        <div class="flex items-end justify-between gap-2" style="height: 160px;">
            @foreach ($summary['last7Days'] as $day)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <div class="font-body-sm text-[10px] text-on-surface-variant mb-1">
                        @if($day['total'] > 0){{ number_format($day['total'] / 1000, 0, ',', '.') }}@endif
                    </div>
                    <div class="w-full max-w-[28px] bg-primary rounded"
                         style="height: {{ max(4, ($day['total'] / $max) * 120) }}px;"></div>
                    <div class="font-body-sm text-[11px] text-outline mt-1">{{ $day['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="font-body-sm text-[11px] text-outline mt-3 text-center">Angka dalam ribuan rupiah</div>
    </div>
@endsection
