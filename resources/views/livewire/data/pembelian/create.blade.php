<?php

use Livewire\Volt\Component;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\StockOpname;
use Livewire\Attributes\Rule;

new class extends Component
{
    #[Rule('required|numeric')]
    public string $kode_item = '';

    public ?int $stok_saat_ini = null;

    #[Rule('required|string|max:255')]
    public string $nama_item = '';

    #[Rule('required|string')]
    public string $jenis = '';

    #[Rule('required|numeric|min:1')]
    public string $jumlah = '';

    #[Rule('required|string')]
    public string $satuan = '';

    #[Rule('required|numeric|min:0')]
    public string $total_harga = '';

    #[Rule('required|string')]
    public string $bulan = '';

    #[Rule('required|numeric|min:2000|max:2099')]
    public string $tahun = '';

    public function updatedKodeItem($value)
    {
        $this->reset(['nama_item', 'jenis', 'satuan', 'stok_saat_ini', 'jumlah']);
        if (!empty($value)) {
            $barang = Barang::where('Kode_Item', (int)$value)->first();
            $stock = StockOpname::where('Kode_Item', (int)$value)->first();

            if ($barang) {
                $this->nama_item = $barang->Nama_Item;
                $this->jenis = $barang->Jenis;
                $this->satuan = $barang->Satuan ?? '';
                $this->stok_saat_ini = $stock ? $stock->Stok_Sistem : 0;
            }
        }
    }

    public function save()
    {
        $this->validate();

        // --- BAGIAN KUNCI YANG DIPERBAIKI ---
        // 1. Petakan properti ke nama kolom database yang benar
        Pembelian::create([
            'Kode_Item'   => (int) $this->kode_item,
            'Nama_Item'   => $this->nama_item,
            'Jenis'       => $this->jenis,
            'Jumlah'      => (int) $this->jumlah,
            'Satuan'      => strtoupper($this->satuan),
            'Total_Harga' => (int) $this->total_harga,
            'Bulan'       => strtoupper($this->bulan),
            'Tahun'       => (int) $this->tahun,
        ]);
        // --- AKHIR BAGIAN PERBAIKAN ---

        $stockItem = StockOpname::firstOrCreate(
            ['Kode_Item' => (int)$this->kode_item],
            ['Nama_Item' => $this->nama_item]
        );
        $stockItem->increment('Stok_Masuk', (int)$this->jumlah);

        session()->flash('success', 'Data pembelian berhasil ditambahkan dan stok telah diperbarui.');
        return $this->redirectRoute('pembelian.index', navigate: true);
    }
};
?>

@section('title', 'Pembelian')
<div>
    {{-- Tampilan Blade tidak berubah dari sebelumnya --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Pembelian /</span> Tambah Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Tambah Data Pembelian</h5>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control @error('kode_item') is-invalid @enderror" id="kode_item" wire:model.live.debounce.300ms="kode_item" placeholder="Ketik Kode Item untuk mencari otomatis...">
                    @error('kode_item')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @if ($stok_saat_ini !== null)
                    <p class="mt-2 text-success fw-bold">Stok Saat Ini: {{ number_format($stok_saat_ini, 0, ',', '.') }}</p>
                    @elseif (!empty($kode_item) && empty($nama_item))
                    <p class="mt-2 text-danger">Barang dengan kode ini tidak ditemukan di master barang.</p>
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
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" wire:model="jumlah" placeholder="Contoh: 100">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuan" wire:model="satuan" placeholder="Contoh: PCS, BOX, LUSIN">
                        @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="total_harga" class="form-label">Total Harga (Rp)</label>
                    <input type="number" class="form-control @error('total_harga') is-invalid @enderror" id="total_harga" wire:model="total_harga" placeholder="Contoh: 1500000">
                    @error('total_harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>