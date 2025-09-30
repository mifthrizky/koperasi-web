<?php

use Livewire\Volt\Component;
use App\Models\Barang;
use Livewire\Attributes\Rule;

new class extends Component
{
    #[Rule('required|numeric|unique:barangs,Kode_Item')]
    public int $kode_item = 0;

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|string|max:100')]
    public string $jenis = '';

    #[Rule('required|numeric|min:0')]
    public int $harga_satuan = 0;

    /**
     * Simpan data barang baru.
     */
    public function save()
    {
        $this->validate();

        Barang::create([
            'Kode_Item'    => $this->kode_item,
            'Nama_Item'    => $this->nama_item,
            'Jenis'        => $this->jenis,
            'Harga_Satuan' => $this->harga_satuan,
        ]);

        session()->flash('success', 'Data barang berhasil ditambahkan.');
        return $this->redirectRoute('barang.index', navigate: true);
    }
}; ?>

<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Barang /</span> Tambah Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Tambah Data Barang</h5>
        </div>
        <div class="card-body">

            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control @error('kode_item') is-invalid @enderror"
                        id="kode_item" wire:model="kode_item" placeholder="Contoh: 101">
                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control @error('nama_item') is-invalid @enderror"
                        id="nama_item" wire:model="nama_item" placeholder="Contoh: Aqua Botol 600ml">
                    @error('nama_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control @error('jenis') is-invalid @enderror"
                        id="jenis" wire:model="jenis" placeholder="Contoh: Minuman">
                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="harga_satuan" class="form-label">Harga Satuan (Rp)</label>
                    <input type="number" class="form-control @error('harga_satuan') is-invalid @enderror"
                        id="harga_satuan" wire:model="harga_satuan" placeholder="Contoh: 1500">
                    @error('harga_satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>