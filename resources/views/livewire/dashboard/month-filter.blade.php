<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On; 

new class extends Component
{
    // Inisialisasi properti
    public string $selectedMonth = '';
    public string $selectedYear = '';

    // Ketika $selectedMonth diubah, kirim event
    public function updatedSelectedMonth($value)
    {
        // Kirim event dengan nilai filter saat ini
        $this->dispatch('filter-updated', month: $value, year: $this->selectedYear);
    }

    // Ketika $selectedYear diubah, kirim event
    public function updatedSelectedYear($value)
    {
        // Kirim event dengan nilai filter saat ini
        $this->dispatch('filter-updated', month: $this->selectedMonth, year: $value);
    }

    // ... (Fungsi months() dan years() tetap sama) ...

    #[Computed]
    public function months()
    {
        // ... (Kode untuk mengambil bulan unik dari DB) ...
        $bulanDariDB = DB::getMongoDB()
            ->selectCollection('penjualans')
            ->distinct('Bulan');

        $bulanDariDB = array_map(fn($b) => strtoupper(trim($b)), $bulanDariDB);

        $urutanBulan = [
            'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
            'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
        ];

        return array_values(array_intersect($urutanBulan, $bulanDariDB));
    }

    #[Computed]
    public function years()
    {
        // ... (Kode untuk mengambil tahun unik dari DB) ...
        $tahunDariDB = DB::getMongoDB()
            ->selectCollection('penjualans')
            ->distinct('Tahun');

        $urutanTahun = [2020, 2021, 2022, 2023, 2024, 2025];

        return array_values(array_intersect($urutanTahun, $tahunDariDB));
    }
    

    // Hapus kueri $penjualans dari fungsi with() karena itu harusnya ada di komponen lain.
    public function with(): array
    {
        return [
            // Cukup kembalikan data untuk dropdown
            'months' => $this->months(),
            'years' => $this->years(),
        ];
    }
};
?>

<div class="d-flex justify-content-end mb-3 gap-2">
    <!-- Dropdown Bulan -->
    <select wire:model.live="selectedMonth" class="form-select w-auto">
        <option value="">Semua Bulan</option>
        @foreach($months as $month)
            <option value="{{ $month }}">{{ strtoupper($month) }}</option>
        @endforeach
    </select>

    <!-- Dropdown Tahun -->
    <select wire:model.live="selectedYear" class="form-select w-auto">
        <option value="">Semua Tahun</option>
        @foreach($years as $year)
            <option value="{{ $year }}">{{ $year }}</option>
        @endforeach
    </select>
</div>
