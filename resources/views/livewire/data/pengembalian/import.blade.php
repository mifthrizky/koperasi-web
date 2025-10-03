<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use App\Imports\RetursImport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithFileUploads;

    // Properti untuk menampung file yang di-upload
    #[Rule('required|mimes:xlsx,xls')]
    public $file;

    /**
     * Proses file excel yang di-upload.
     */
    public function import()
    {
        $this->validate();

        try {
            Excel::import(new RetursImport, $this->file);

            session()->flash('success', 'Data pengembalian berhasil diimpor!');
            $this->dispatch('import-success', url: route('pengembalian.index'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Tangani error validasi dari Excel
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
}; ?>

@section('title', 'Impor Retur')
<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Retur /</span> Impor Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Impor Data Retur dari Excel</h5>
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
                <p>Pastikan file Excel yang Anda unggah memiliki format yang benar dengan header. Anda dapat mengunduh template yang sudah kami sediakan.</p>
                <hr>
                <a href="{{ asset('templates/template_pengembalian.xlsx') }}" class="btn btn-sm btn-primary" download>
                    <i class="bx bx-download me-1"></i> Download Template
                </a>
            </div>

            <form wire:submit.prevent="import" class="mt-4">
                <div class="mb-3">
                    <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" wire:model="file">
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('pengembalian.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="import">Impor Data</span>
                        <span wire:loading wire:target="import">Mengimpor... <i class="bx bx-loader-alt bx-spin"></i></span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>