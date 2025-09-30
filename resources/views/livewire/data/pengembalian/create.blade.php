<?php

use Livewire\Volt\Component;
use App\Models\Retur;
use App\Models\Pembelian;
use Livewire\Attributes\Rule;

new class extends Component
{
    #[Rule('required|numeric|unique:returs,Kode_Item')]
    public string $kode_item = '';

    public ?int $stok_tersedia = null;

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|string')]
    public string $jenis = '';

    #[Rule('required|numeric|min:1')]
    public string $jumlah = '';

    public string $satuan = ''; // otomatis dari pembelian

    public string $harga_satuan = ''; // untuk perhitungan harga

    #[Rule('required|numeric|min:0')]
    public string $total_harga_pengembalian = '';

    #[Rule('required|string')]
    public string $bulan = '';

    #[Rule('required|numeric|min:2000|max:2099')]
    public string $tahun = '';

    public function updatedKodeItem($value)
    {
        $this->reset(['stok_tersedia','nama_item','jenis','satuan','harga_satuan','total_harga_pengembalian']);

        if (!empty($value)) {
            $pembelian = Pembelian::where('Kode_Item', (int)$value)
                                  ->latest('created_at')
                                  ->first();

            if ($pembelian) {
                $this->stok_tersedia = $pembelian->Jumlah; 
                $this->nama_item = $pembelian->Nama_Item;
                $this->jenis = $pembelian->Jenis;
                $this->satuan = $pembelian->Satuan; // ambil otomatis
                $this->harga_satuan = $pembelian->Total_Harga / max(1, $pembelian->Jumlah); // hitung harga per unit
            }
        }
    }

    public function updatedJumlah($value)
    {
        if (!empty($value) && is_numeric($value) && $this->harga_satuan) {
            $this->total_harga_pengembalian = (string) ((int)$value * (int)$this->harga_satuan);
        } else {
            $this->total_harga_pengembalian = '';
        }
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->stok_tersedia !== null && (int)$this->jumlah > $this->stok_tersedia) {
            $this->addError('jumlah', 'Jumlah retur melebihi stok yang tersedia ('.$this->stok_tersedia.').');
            return;
        }

        Retur::create([
            'Kode_Item' => (int) $this->kode_item,
            'Nama_Item' => $this->nama_item,
            'Jenis' => $this->jenis,
            'Jumlah' => (int) $this->jumlah,
            'Satuan' => strtoupper($this->satuan),
            'Total_Harga_Pengembalian' => (int) $this->total_harga_pengembalian,
            'Bulan' => strtoupper($this->bulan),
            'Tahun' => (int) $this->tahun,
        ]);

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
                    <div class="input-group">
                        <input type="number" class="form-control @error('kode_item') is-invalid @enderror" id="kode_item" wire:model.live="kode_item" placeholder="Contoh: 101">
                        <button type="button" class="btn btn-outline-primary" wire:click="updatedKodeItem($event.target.previousElementSibling.value)">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    
                    @if ($stok_tersedia !== null)
                        <p class="mt-2 text-success fw-bold">Stok Tersedia: {{ number_format($stok_tersedia, 0, ',', '.') }}</p>
                    @elseif (!empty($kode_item))
                        <p class="mt-2 text-warning">Stok tidak ditemukan di data Pembelian.</p>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control" id="nama_item" wire:model="nama_item" readonly>
                </div>
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control" id="jenis" wire:model="jenis" readonly>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" wire:model="jumlah" placeholder="Contoh: 50">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="satuan" wire:model="satuan" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="total_harga_pengembalian" class="form-label">Total Harga Retur (Rp)</label>
                    <input type="number" class="form-control" id="total_harga_pengembalian" wire:model="total_harga_pengembalian" readonly>
                </div>
                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <input type="text" class="form-control" id="bulan" wire:model="bulan" placeholder="Contoh: JANUARI">
                </div>
                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" class="form-control" id="tahun" wire:model="tahun" placeholder="Contoh: 2025">
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
