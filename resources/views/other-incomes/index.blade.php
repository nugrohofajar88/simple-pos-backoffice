@extends('layouts.app')

@section('title', 'Pendapatan Lain')

@section('content')
    <div x-data class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Pendapatan Lain</h1>
            <button @click="$refs.newIncomeForm.classList.toggle('hidden')"
                    class="bg-gray-900 text-white text-sm px-3 py-2 rounded">+ Tambah</button>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form x-ref="newIncomeForm" method="POST" action="{{ route('other-incomes.store') }}"
              class="hidden bg-white border rounded-lg p-4 flex flex-col sm:flex-row gap-2 sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium mb-1">Keterangan</label>
                <input type="text" name="description" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:w-40">
                <label class="block text-xs font-medium mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" required min="1" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded">Simpan</button>
        </form>
    </div>

    <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead class="bg-gray-50 text-left text-xs text-gray-500">
                <tr>
                    <th class="px-4 py-2">Keterangan</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2 text-right">Jumlah</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($otherIncomes as $income)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium">{{ $income->description }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ ($income->mobile_created_at ?? $income->created_at)->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right">Rp{{ number_format($income->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('other-incomes.destroy', $income) }}"
                                  onsubmit="return confirm('Hapus catatan pendapatan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada catatan pendapatan lain.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $otherIncomes->links() }}</div>
@endsection
