<?php

use Livewire\Volt\Component;
use App\Models\Retur; // Diubah dari Pengembalian ke Retur
use Livewire\Attributes\Rule;

new class extends Component
{
    #[Rule('required|numeric|unique:returs,Kode_Item')] // Updated table name to returs
    public string $kode_item = '';

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|string')]
    public string $jenis = '';

    #[Rule('required|numeric|min:1')]
    public string $jumlah = '';

    #[Rule('required|string')]
    public string $satuan = '';

    #[Rule('required|numeric|min:0')]
    public string $total_harga_pengembalian = '';

    #[Rule('required|string')]
    public string $alasan = '';

    #[Rule('required|string')]
    public string $bulan = '';

    #[Rule('required|numeric|min:2000|max:2099')]
    public string $tahun = '';

    /**
     * Simpan data retur baru.
     */
    public function save()
    {
        // Validasi data
        $validated = $this->validate();

        // Buat data baru
        Retur::create([ // Menggunakan model Retur
            'Kode_Item' => (int) $this->kode_item,
            'Nama_Item' => $this->nama_item,
            'Jenis' => $this->jenis,
            'Jumlah' => (int) $this->jumlah,
            'Satuan' => strtoupper($this->satuan),
            'Total_Harga_Pengembalian' => (int) $this->total_harga_pengembalian,
            'Alasan' => $this->alasan,
            'Bulan' => strtoupper($this->bulan),
            'Tahun' => (int) $this->tahun,
        ]);

        // Kirim pesan sukses dan redirect
        session()->flash('success', 'Data retur berhasil ditambahkan.');
        return $this->redirectRoute('pengembalian.index', navigate: true);
    }
}; ?>

<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Retur /</span> Tambah Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Tambah Data Retur</h5>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control @error('kode_item') is-invalid @enderror" id="kode_item" wire:model="kode_item" placeholder="Contoh: 101">
                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control @error('nama_item') is-invalid @enderror" id="nama_item" wire:model="nama_item" placeholder="Contoh: Gula Pasir">
                    @error('nama_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control @error('jenis') is-invalid @enderror" id="jenis" wire:model="jenis" placeholder="Contoh: Sembako">
                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" wire:model="jumlah" placeholder="Contoh: 50">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuan" wire:model="satuan" placeholder="Contoh: PCS, KG">
                        @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="total_harga_pengembalian" class="form-label">Total Harga Retur (Rp)</label>
                    <input type="number" class="form-control @error('total_harga_pengembalian') is-invalid @enderror" id="total_harga_pengembalian" wire:model="total_harga_pengembalian" placeholder="Contoh: 750000">
                    @error('total_harga_pengembalian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="alasan" class="form-label">Alasan Retur</label>
                    <textarea class="form-control @error('alasan') is-invalid @enderror" id="alasan" wire:model="alasan" rows="3" placeholder="Contoh: Barang cacat, Salah kirim"></textarea>
                    @error('alasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <input type="text" class="form-control @error('bulan') is-invalid @enderror" id="bulan" wire:model="bulan" placeholder="Contoh: JANUARI">
                    @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" class="form-control @error('tahun') is-invalid @enderror" id="tahun" wire:model="tahun" placeholder="Contoh: 2025">
                    @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('pengembalian.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
