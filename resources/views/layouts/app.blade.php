<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    <div class="md:hidden flex items-center justify-between bg-gray-900 text-white px-4 py-3 sticky top-0 z-20">
        <span class="font-semibold">{{ config('app.name') }}</span>
        <button @click="sidebarOpen = true" class="p-1 text-2xl leading-none" aria-label="Buka menu">&#9776;</button>
    </div>

    <div class="flex min-h-screen">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/50 md:hidden"></div>

        <aside
            x-cloak
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 bg-gray-900 text-gray-200 flex flex-col transform transition-transform duration-200 ease-in-out md:static md:z-auto md:w-56 md:translate-x-0">
            <div class="px-4 py-5 text-lg font-semibold text-white border-b border-gray-800 flex items-center justify-between">
                {{ config('app.name') }}
                <button @click="sidebarOpen = false" class="md:hidden text-xl leading-none" aria-label="Tutup menu">&times;</button>
            </div>
            <nav class="flex-1 px-2 py-4 space-y-1">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard'],
                        ['route' => 'menu.index', 'label' => 'Menu'],
                        ['route' => 'orders.index', 'label' => 'Riwayat Order'],
                        ['route' => 'expenses.index', 'label' => 'Belanja'],
                        ['route' => 'other-incomes.index', 'label' => 'Pendapatan Lain'],
                        ['route' => 'settings.index', 'label' => 'Pengaturan'],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="block rounded px-3 py-2 text-sm {{ request()->routeIs($item['route'].'*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="px-2 py-4 border-t border-gray-800">
                @csrf
                <button type="submit" class="w-full text-left rounded px-3 py-2 text-sm text-red-300 hover:bg-gray-800">
                    Logout
                </button>
            </form>
        </aside>

        <main class="flex-1 w-full min-w-0 p-4 md:p-6">
            @if (session('status'))
                <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-2 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
