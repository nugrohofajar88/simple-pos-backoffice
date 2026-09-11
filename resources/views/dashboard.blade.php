@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 11 => 'Pagi',
            $hour < 15 => 'Siang',
            $hour < 18 => 'Sore',
            default => 'Malam',
        };
    @endphp
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Selamat {{ $greeting }}, {{ auth()->user()->name }}! ☕</h1>

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-secondary text-title-lg">bar_chart</span>
                <span class="font-title-md text-title-md font-semibold text-on-surface">Omzet 7 Hari Terakhir</span>
            </div>
            <div style="height: 220px;">
                <canvas id="omzetChart"></canvas>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-secondary text-title-lg">local_cafe</span>
                <span class="font-title-md text-title-md font-semibold text-on-surface">Top Menu (Bulan Ini)</span>
            </div>
            @forelse ($summary['topProducts'] as $index => $product)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-outline-variant' : '' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 shrink-0 rounded-full bg-secondary-fixed flex items-center justify-center font-label-sm text-label-sm font-bold text-on-secondary-fixed">{{ $index + 1 }}</div>
                        <div class="min-w-0">
                            <div class="font-label-lg text-label-lg text-on-surface font-medium truncate">{{ $product['name'] }}</div>
                            <div class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $product['qty'] }} terjual · Rp{{ number_format($product['revenue'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <span class="shrink-0 ml-2 px-2 py-0.5 bg-tertiary-fixed text-on-tertiary-fixed-variant font-label-sm text-label-sm rounded-full">{{ $product['marginPercent'] }}%</span>
                </div>
            @empty
                <p class="font-body-sm text-body-sm text-on-surface-variant">Belum ada penjualan bulan ini.</p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.x.x/dist/chart.umd.min.js"></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('omzetChart');
            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, 'rgba(153, 71, 3, 0.35)');
            gradient.addColorStop(1, 'rgba(153, 71, 3, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json(collect($summary['last7Days'])->pluck('label')),
                    datasets: [{
                        label: 'Omzet',
                        data: @json(collect($summary['last7Days'])->pluck('total')),
                        borderColor: '#994703',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#994703',
                        pointRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: (value) => 'Rp' + (value / 1000) + 'rb' },
                            grid: { color: '#EAE3D9' },
                        },
                        x: { grid: { display: false } },
                    },
                },
            });
        });
    </script>
@endpush
