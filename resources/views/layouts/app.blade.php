<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @include('layouts.tailwind-config')
</head>
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased" x-data="{ sidebarOpen: false }">
    <div class="md:hidden flex items-center justify-between bg-primary-container text-surface-bright px-4 py-3 sticky top-0 z-20">
        <span class="font-title-md text-title-md font-bold">{{ config('app.name') }}</span>
        <button @click="sidebarOpen = true" class="p-1 material-symbols-outlined" aria-label="Buka menu">menu</button>
    </div>

    <div class="flex min-h-screen">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/50 md:hidden"></div>

        <aside
            x-cloak
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 bg-primary-container text-surface-bright flex flex-col transform transition-transform duration-200 ease-in-out md:static md:z-auto md:w-64 md:translate-x-0">
            <div class="px-space-md py-5 font-title-md text-title-md font-bold text-surface-container-lowest border-b border-outline-variant/20 flex items-center justify-between">
                {{ config('app.name') }}
                <button @click="sidebarOpen = false" class="md:hidden material-symbols-outlined" aria-label="Tutup menu">close</button>
            </div>
            <nav class="flex-1 px-2 py-4 space-y-1">
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
                       class="flex items-center gap-space-sm rounded-lg px-3 py-2.5 font-label-lg text-label-lg transition-all {{ request()->routeIs($item['route'].'*') ? 'bg-secondary text-on-secondary font-semibold' : 'text-primary-fixed-dim hover:bg-surface-container-highest/10 hover:text-surface-container-lowest' }}">
                        <span class="material-symbols-outlined text-title-lg">{{ $item['icon'] }}</span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="px-2 py-4 border-t border-outline-variant/20">
                @csrf
                <button type="submit" class="w-full flex items-center gap-space-sm rounded-lg px-3 py-2.5 font-label-lg text-label-lg text-error-container hover:bg-surface-container-highest/10">
                    <span class="material-symbols-outlined text-title-lg">logout</span>
                    Logout
                </button>
            </form>
        </aside>

        <main class="flex-1 w-full min-w-0 p-4 md:p-6">
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
