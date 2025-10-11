<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use App\Imports\StockOpnamesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithFileUploads;

    // 1. Tambahkan properti untuk menampung pilihan bulan dan tahun
    public string $selectedMonth = '';
    public string $selectedYear = '';

    // 2. Modifikasi aturan validasi
    #[Rule([
        'file' => 'required|mimes:xlsx,xls',
        'selectedMonth' => 'required',
        'selectedYear' => 'required',
    ], message: [
        'file.required' => 'File Excel wajib diunggah.',
        'selectedMonth.required' => 'Bulan wajib dipilih.',
        'selectedYear.required' => 'Tahun wajib dipilih.',
    ])]
    public $file;

    // Fungsi mount untuk mengisi tahun saat komponen dimuat
    public function mount()
    {
        $this->selectedYear = date('Y'); // Default ke tahun sekarang
        // Tambahkan ini untuk mengatur bulan default ke bulan saat ini
        $months = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $currentMonthIndex = date('n') - 1; // date('n') -> 1 s.d. 12, index array -> 0 s.d. 11
        $this->selectedMonth = $months[$currentMonthIndex];
    }

    public function import()
    {
        // 3. Validasi akan otomatis memeriksa file, bulan, dan tahun
        $this->validate();

        try {
            $petugasName = Auth::user()->name;

            // 4. Gunakan bulan dan tahun dari properti, bukan tanggal saat ini
            Excel::import(new StockOpnamesImport($this->selectedMonth, (int)$this->selectedYear, $petugasName), $this->file);

            session()->flash('success', "Data stok fisik untuk bulan {$this->selectedMonth} {$this->selectedYear} berhasil diimpor!");
            $this->dispatch('import-success', url: route('stock-opname.index'));
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // 5. Siapkan data untuk dikirim ke view (dropdown)
    public function with(): array
    {
        $years = range(date('Y'), date('Y') - 5); // 5 tahun ke belakang
        $months = [
            'JANUARI',
            'FEBRUARI',
            'MARET',
            'APRIL',
            'MEI',
            'JUNI',
            'JULI',
            'AGUSTUS',
            'SEPTEMBER',
            'OKTOBER',
            'NOVEMBER',
            'DESEMBER'
        ];

        return [
            'years' => $years,
            'months' => $months,
        ];
    }
}; ?>

@section('title', 'Impor Stok Fisik')
<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Stock Opname /</span> Impor Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Impor Stok Fisik dari Excel</h5>
        </div>
        <div class="card-body">

            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info" role="alert">
                <h6 class="alert-heading">Petunjuk!</h6>
                <p>
                    Pilih Bulan dan Tahun terlebih dahulu. Lalu unggah file Excel dengan header: <strong>kode_item</strong>, <strong>nama_barang</strong> (opsional), dan <strong>qty_so</strong>.
                </p>
                <hr>
                <a href="{{ asset('templates/template_stock_opname_simple.xlsx') }}" class="btn btn-sm btn-primary" download>
                    <i class="bx bx-download me-1"></i> Download Template
                </a>
            </div>

            <form wire:submit.prevent="import" class="mt-4">
                <div class="row mb-3">
                    <!-- Dropdown bulan, tahun -->
                    <div class="col-md-6">
                        <label for="month" class="form-label">Pilih Bulan Stok Opname</label>
                        <select id="month" class="form-select @error('selectedMonth') is-invalid @enderror" wire:model.live="selectedMonth">
                            <option value="" disabled>-- Pilih Bulan --</option>
                            @foreach ($months as $month)
                            <option value="{{ $month }}">{{ ucfirst(strtolower($month)) }}</option>
                            @endforeach
                        </select>
                        @error('selectedMonth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="year" class="form-label">Pilih Tahun</label>
                        <select id="year" class="form-select @error('selectedYear') is-invalid @enderror" wire:model.live="selectedYear">
                            <option value="" disabled>-- Pilih Tahun --</option>
                            @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        @error('selectedYear') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" wire:model="file">
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('stock-opname.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="import">Impor Data</span>
                        <span wire:loading wire:target="import">Mengimpor... <i class="bx bx-loader-alt bx-spin"></i></span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>