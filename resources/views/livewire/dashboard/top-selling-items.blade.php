<?php

use Livewire\Volt\Component;
use App\Models\Penjualan; // Pastikan Anda memiliki model ini

new class extends Component
{
    /**
     * Menyediakan data untuk di-render di view.
     */
    public function with(): array
    {
        // Query ke collection 'penjualans' untuk mendapatkan 10 item
        // dengan 'Stok_Keluar' tertinggi.
        $topItems = Penjualan::orderBy('Stok_Keluar', 'desc')
            ->take(10)
            ->get();

        return [
            'topItems' => $topItems,
        ];
    }
}; ?>

<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">📊 10 Barang Terlaris</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Nama Item</th>
                        <th>Jenis</th>
                        <th class="text-end">Jumlah Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topItems as $item)
                    <tr wire:key="{{ $item->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->Nama_Item }}</strong></td>
                        <td><span class="badge bg-label-primary">{{ $item->Jenis }}</span></td>
                        <td class="text-end">{{ $item->Stok_Keluar }} {{ $item->Satuan }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            Belum ada data penjualan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>