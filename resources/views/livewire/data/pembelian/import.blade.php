<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use App\Imports\PembeliansImport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithFileUploads;

    // Pilihan bulan dan tahun
    public string $selectedMonth = '';
    public string $selectedYear = '';

    // Properti untuk menampung file yang di-upload
    #[Rule([
        'file' => 'required|mimes:xlsx,xls',
        'selectedMonth' => 'required',
        'selectedYear' => 'required',
    ], message: [
        'file' => 'File wajib di unggah',
        'selectedMonth.required' => 'Bulan wajib dipilih',
        'selectedYear.required' => 'Tahun wajib dipilih',
    ])]
    public $file;

    // Memilih bulan saat refresh
    public function mount()
    {
        $this->selectedYear = date('Y');
    }

    /**
     * Proses file excel yang di-upload.
     */
    public function import()
    {
        $this->validate();

        try {
            Excel::import(new PembeliansImport($this->selectedMonth, (int)$this->selectedYear), $this->file);

            session()->flash('success', "Data pembelian untuk bulan {$this->selectedMonth} {$this->selectedYear} berhasil diimpor!");
            $this->dispatch('import-success', url: route('pembelian.index'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            session()->flash('error', 'Terjadi kesalahan validasi: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Fungsi load tahun, bulan ke dropdowm
    public function with(): array
    {
        $years = range(date('Y'), date('Y') - 5);
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
            'months' => $months
        ];
    }
}; ?>

@section('title', 'Impor Pembelian')
<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Pembelian /</span> Impor Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Impor Data Pembelian dari Excel</h5>
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
                <p>Pastikan file Excel yang Anda unggah memiliki format yang benar. Anda dapat mengunduh template yang sudah kami sediakan.</p>
                <hr>
                <a href="{{ asset('templates/template_pembelian.xlsx') }}" class="btn btn-sm btn-primary" download>
                    <i class="bx bx-download me-1"></i> Download Template
                </a>
            </div>

            <form wire:submit.prevent="import" class="mt-4">
                <!-- Dropdown bulan, tahun -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="month" class="form-label">Pilih Bulan Pembelian</label>
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
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="import">Impor Data</span>
                        <span wire:loading wire:target="import">Mengimpor... <i class="bx bx-loader-alt bx-spin"></i></span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>