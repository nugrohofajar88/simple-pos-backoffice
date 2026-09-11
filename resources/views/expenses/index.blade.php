@extends('layouts.app')

@section('title', 'Belanja')

@section('content')
    <div x-data class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-headline-sm text-headline-sm text-primary">Belanja</h1>
            <button @click="$refs.newExpenseForm.classList.toggle('hidden')"
                    class="bg-primary text-on-primary font-label-lg text-label-lg px-3 py-2 rounded-lg">+ Tambah</button>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
                {{ $errors->first() }}
            </div>
        @endif

        <form x-ref="newExpenseForm" method="POST" action="{{ route('expenses.store') }}"
              class="hidden bg-surface-container-lowest border border-outline-variant rounded-xl p-4 flex flex-col sm:flex-row gap-2 sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Keterangan</label>
                <input type="text" name="description" required class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <div class="sm:w-40">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" required min="1" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <button type="submit" class="bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-lg">Simpan</button>
        </form>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto">
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
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-surface-container-low">
                        <td class="px-4 py-2 font-medium text-on-surface">{{ $expense->description }}</td>
                        <td class="px-4 py-2 text-on-surface-variant">{{ ($expense->mobile_created_at ?? $expense->created_at)->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right text-on-surface">Rp{{ number_format($expense->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                  onsubmit="return confirm('Hapus catatan belanja ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="font-label-sm text-label-sm text-error">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-outline">Belum ada catatan belanja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>
@endsection
