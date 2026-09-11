@extends('layouts.app')

@section('title', 'Pendapatan Lain')

@section('content')
    <div x-data class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-headline-sm text-headline-sm text-primary">Pendapatan Lain</h1>
            <button @click="$refs.newIncomeForm.classList.toggle('hidden')"
                    class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-3 py-2 rounded-xl shadow-sm">
                <span class="material-symbols-outlined text-title-md">add</span> Tambah
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
                {{ $errors->first() }}
            </div>
        @endif

        <form x-ref="newIncomeForm" method="POST" action="{{ route('other-incomes.store') }}"
              class="hidden bg-surface-container-lowest rounded-xl shadow-sm p-4 flex flex-col sm:flex-row gap-2 sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Keterangan</label>
                <input type="text" name="description" required class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <div class="sm:w-40">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" required min="1" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-xl">
                <span class="material-symbols-outlined text-title-md">save</span> Simpan
            </button>
        </form>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-body-md min-w-[560px]">
            <thead class="bg-surface-container-low text-left font-label-sm text-label-sm text-on-surface-variant">
                <tr>
                    <th class="px-4 py-2">Keterangan</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2 text-right">Jumlah</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse ($otherIncomes as $income)
                    <tr class="hover:bg-surface-container-low">
                        <td class="px-4 py-2 font-medium text-on-surface">{{ $income->description }}</td>
                        <td class="px-4 py-2 text-on-surface-variant">{{ ($income->mobile_created_at ?? $income->created_at)->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right text-on-surface">Rp{{ number_format($income->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('other-incomes.destroy', $income) }}"
                                  onsubmit="return confirm('Hapus catatan pendapatan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="font-label-sm text-label-sm text-error">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-outline">Belum ada catatan pendapatan lain.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $otherIncomes->links() }}</div>
@endsection
