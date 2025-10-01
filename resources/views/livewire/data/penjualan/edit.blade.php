<?php

use Livewire\Volt\Component;
use App\Models\Penjualan;
use App\Models\StockOpname; // Import StockOpname
use Livewire\Attributes\Rule;

new class extends Component
{
    public Penjualan $penjualan;

    #[Rule('required|numeric')]
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
    public string $total_harga = '';

    #[Rule('required|string')]
    public string $bulan = '';

    public function mount(Penjualan $penjualan)
    {
        $this->penjualan   = $penjualan;
        $this->kode_item   = $penjualan->Kode_Item;
        $this->nama_item   = $penjualan->Nama_Item;
        $this->jenis       = $penjualan->Jenis;
        $this->jumlah      = $penjualan->Jumlah;
        $this->satuan      = $penjualan->Satuan;
        $this->total_harga = $penjualan->Total_Harga;
        $this->bulan       = $penjualan->Bulan;
    }

    public function update()
    {

        // 1. Hitung selisih antara jumlah lama dan jumlah baru
        $jumlahLama = $this->penjualan->Jumlah;
        $jumlahBaru = (int) $this->jumlah;
        $selisih = $jumlahBaru - $jumlahLama;

        // 2. Jika ada perubahan jumlah, update stok
        if ($selisih !== 0) {
            $stockItem = StockOpname::where('Kode_Item', (int)$this->penjualan->Kode_Item)->first();
            if ($stockItem) {
                // increment bisa menerima nilai positif (menambah) atau negatif (mengurangi)
                $stockItem->increment('Stok_Keluar', $selisih);
            }
        }

        // 3. Update data penjualannya dengan memetakan properti ke nama kolom yang benar
        $this->penjualan->update([
            'Kode_Item'   => (int) $this->kode_item,
            'Nama_Item'   => $this->nama_item,
            'Jenis'       => $this->jenis,
            'Jumlah'      => (int) $this->jumlah,
            'Satuan'      => $this->satuan,
            'Total_Harga' => (int) $this->total_harga,
            'Bulan'       => strtoupper($this->bulan),
        ]);

        session()->flash('success', 'Data penjualan berhasil diperbarui dan stok telah disesuaikan.');
        return $this->redirectRoute('penjualan.index', navigate: true);
    }
};
?>

@section('title', 'Penjualan')
<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Penjualan /</span> Edit Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Edit Data Penjualan</h5>
        </div>
        <div class="card-body">
            <form wire:submit="update">
                <div class="mb-3">
                    <label for="kode_item" class="form-label">Kode Item</label>
                    <input type="number" class="form-control @error('kode_item') is-invalid @enderror"
                        id="kode_item" wire:model="kode_item" readonly>
                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="nama_item" class="form-label">Nama Item</label>
                    <input type="text" class="form-control @error('nama_item') is-invalid @enderror"
                        id="nama_item" wire:model="nama_item" readonly>
                    @error('nama_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control @error('jenis') is-invalid @enderror"
                        id="jenis" wire:model="jenis" readonly>
                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror"
                            id="jumlah" wire:model="jumlah" placeholder="Contoh: 50">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control @error('satuan') is-invalid @enderror"
                            id="satuan" wire:model="satuan">
                        @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="total_harga" class="form-label">Total Harga (Rp)</label>
                    <input type="number" class="form-control @error('total_harga') is-invalid @enderror"
                        id="total_harga" wire:model="total_harga" placeholder="Contoh: 750000">
                    @error('total_harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <input type="text" class="form-control @error('bulan') is-invalid @enderror"
                        id="bulan" wire:model="bulan" placeholder="Contoh: JANUARI">
                    @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary me-2" wire:navigate>Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>Update</span>
                        <span wire:loading>Memperbarui...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>