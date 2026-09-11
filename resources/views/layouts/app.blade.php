<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @include('layouts.tailwind-config')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
@php $storeName = \App\Models\Setting::getValue('store_name') ?: config('app.name'); @endphp
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased" x-data="{ sidebarOpen: false }">
    <div class="md:hidden flex items-center justify-between bg-primary-container text-surface-bright px-4 py-3 sticky top-0 z-20">
        <span class="font-title-md text-title-md font-bold">{{ $storeName }}</span>
        <button @click="sidebarOpen = true" class="p-1 material-symbols-outlined" aria-label="Buka menu">menu</button>
    </div>

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/50 md:hidden"></div>

    <aside
        x-cloak
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-primary-container text-surface-bright flex flex-col justify-between transform transition-transform duration-200 ease-in-out md:translate-x-0 shadow-[0_1px_8px_rgba(0,0,0,0.2)]">
        <div class="flex flex-col min-h-0 overflow-y-auto">
            <div class="h-16 px-space-md flex items-center gap-space-sm bg-primary/40 shrink-0">
                <div class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center font-title-md text-title-md font-bold text-on-secondary shrink-0">{{ strtoupper(substr($storeName, 0, 1)) }}</div>
                <div class="flex flex-col min-w-0">
                    <span class="font-title-md text-title-md font-bold tracking-tight text-surface-container-lowest leading-none truncate">{{ $storeName }}</span>
                    <span class="font-label-sm text-label-sm text-secondary-fixed-dim uppercase tracking-wider truncate">POS Back-Office</span>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden material-symbols-outlined ml-auto" aria-label="Tutup menu">close</button>
            </div>
            <div class="px-space-md py-space-sm">
                <div class="font-label-sm text-label-sm text-on-primary-container uppercase px-space-sm mb-space-xs font-semibold tracking-wider">Navigasi</div>
                <nav class="flex flex-col gap-1">
                    @php
                        $navItems = [
                            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                            ['route' => 'menu.index', 'label' => 'Menu', 'icon' => 'coffee'],
                            ['route' => 'orders.index', 'label' => 'Riwayat Order', 'icon' => 'receipt'],
                            ['route' => 'expenses.index', 'label' => 'Belanja', 'icon' => 'wallet'],
                            ['route' => 'other-incomes.index', 'label' => 'Pendapatan Lain', 'icon' => 'payments'],
                            ['route' => 'settings.index', 'label' => 'Pengaturan', 'icon' => 'settings'],
                        ];
                    @endphp
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-space-sm px-space-md py-2.5 rounded-lg font-label-lg text-label-lg transition-all {{ request()->routeIs($item['route'].'*') ? 'bg-secondary text-on-secondary font-semibold shadow-[0_1px_8px_rgba(0,0,0,0.08)]' : 'text-primary-fixed-dim hover:bg-surface-container-highest/10 hover:text-surface-container-lowest' }}">
                            <span class="material-symbols-outlined text-title-lg">{{ $item['icon'] }}</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
        <div class="shrink-0">
            <div class="px-space-md py-space-sm bg-primary/30">
                <span class="font-label-sm text-label-sm text-primary-fixed-dim flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-tertiary-fixed"></span> Sistem Terkoneksi
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="px-2 py-3 border-t border-outline-variant/20">
                @csrf
                <button type="submit" class="w-full flex items-center gap-space-sm rounded-lg px-3 py-2.5 font-label-lg text-label-lg text-error-container hover:bg-surface-container-highest/10">
                    <span class="material-symbols-outlined text-title-lg">logout</span>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="md:pl-64 min-h-screen">
        <header class="hidden md:flex fixed top-0 left-64 right-0 h-16 bg-surface/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-20 px-space-lg items-center justify-between">
            <div class="flex items-center gap-space-md">
                <div class="inline-flex items-center gap-space-xs bg-surface-container px-3 py-1.5 rounded-full">
                    <span class="w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-on-surface-variant font-label-md text-label-md bg-surface-container-low px-3 py-1.5 rounded-lg"
                     x-data="{ time: new Date().toLocaleTimeString('id-ID') }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID'), 1000)">
                    <span class="material-symbols-outlined text-body-md">schedule</span>
                    <span x-text="time"></span>
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <button type="button" disabled title="Fitur laci kasir belum tersedia"
                        class="inline-flex items-center gap-1.5 bg-surface-container-high text-on-surface px-3 py-1.5 rounded-lg font-label-md text-label-md opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined text-body-md text-secondary">lock_open</span>
                    <span>Buka Laci</span>
                </button>
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-1.5 bg-secondary hover:bg-secondary/90 text-on-secondary px-3 py-1.5 rounded-lg font-label-md text-label-md transition-colors">
                    <span class="material-symbols-outlined text-body-md">summarize</span>
                    <span>Laporan Singkat</span>
                </a>
                <div class="h-6 w-px bg-outline-variant/40 mx-space-xs"></div>
                <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center font-label-md text-label-md font-semibold text-on-secondary" title="{{ auth()->user()->name }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>
        </header>

        <main class="w-full md:pt-16 p-4 md:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-tertiary-container bg-tertiary-container px-4 py-2 font-body-sm text-body-sm text-on-tertiary-container">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
