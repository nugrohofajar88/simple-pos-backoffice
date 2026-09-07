@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500 mb-1">Omzet Hari Ini</div>
            <div class="text-xl font-bold">Rp{{ number_format($summary['todayTotal'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500 mb-1">Omzet Bulan Ini</div>
            <div class="text-xl font-bold">Rp{{ number_format($summary['monthTotal'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-xs text-gray-500 mb-1">Belanja Bulan Ini</div>
            <div class="text-xl font-bold">Rp{{ number_format($summary['expenseMonthTotal'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg border p-4">
        <div class="text-sm font-semibold mb-4">Omzet 7 Hari Terakhir</div>
        @php $max = max(max(array_column($summary['last7Days'], 'total') ?: [0]), 1); @endphp
        <div class="flex items-end justify-between gap-2" style="height: 160px;">
            @foreach ($summary['last7Days'] as $day)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <div class="text-[10px] text-gray-500 mb-1">
                        @if($day['total'] > 0){{ number_format($day['total'] / 1000, 0, ',', '.') }}@endif
                    </div>
                    <div class="w-full max-w-[28px] bg-gray-900 rounded"
                         style="height: {{ max(4, ($day['total'] / $max) * 120) }}px;"></div>
                    <div class="text-[11px] text-gray-400 mt-1">{{ $day['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="text-[11px] text-gray-400 mt-3 text-center">Angka dalam ribuan rupiah</div>
    </div>
@endsection
