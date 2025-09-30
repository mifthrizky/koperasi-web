<?php

use Livewire\Volt\Component;
use App\Models\Pembelian;
use App\Models\Retur;

new class extends Component
{
    public Retur $retur;

    // Properti dipertahankan dalam camelCase
    public string $kodeItem = '';
    public string $namaItem = '';
    public string $jumlah = '';
    public string $satuan = '';
    public float $harga = 0; // Harga satuan
    public float $totalHarga = 0;
    public string $bulan = '';
    public string $tahun = '';
    public ?string $jenis = ''; // Nullable untuk mencegah error
    public ?int $stok = null; // Nullable

    /**
     * Mount: Mengisi form dengan data yang benar saat halaman dibuka.
     * Detail item diambil dari Pembelian, data transaksi dari Retur.
     */
    public function mount(Retur $retur)
    {
        $this->retur = $retur;

        // 1. Ambil data spesifik dari transaksi Retur
        $this->kodeItem = (string)$retur->Kode_Item;
        $this->jumlah = (string)$retur->Jumlah;
        $this->bulan = $retur->Bulan;
        $this->tahun = (string)$retur->Tahun;
        
        // 2. Cari detail item terbaru dari Pembelian
        $pembelian = Pembelian::where('Kode_Item', (int)$this->kodeItem)->latest('created_at')->first();

        if ($pembelian) {
            $this->namaItem = $pembelian->Nama_Item;
            $this->jenis = $pembelian->Jenis;
            $this->satuan = $pembelian->Satuan;
            $this->stok = $pembelian->Jumlah; // Stok adalah jumlah dari pembelian
            // Hitung harga satuan
            $this->harga = $pembelian->Total_Harga / max(1, $pembelian->Jumlah);
        }
        
        // 3. Hitung total harga awal
        $this->calculateTotal();
    }
    
    /**
     * Fungsi untuk mencari item (dipanggil oleh tombol Cari)
     */
    public function searchItem()
    {
        $this->reset(['namaItem', 'jenis', 'satuan', 'stok', 'harga', 'totalHarga']);
        
        if (!empty($this->kodeItem)) {
            $pembelian = Pembelian::where('Kode_Item', (int)$this->kodeItem)->latest('created_at')->first();

            if ($pembelian) {
                $this->namaItem = $pembelian->Nama_Item;
                $this->jenis = $pembelian->Jenis;
                $this->satuan = $pembelian->Satuan;
                $this->stok = $pembelian->Jumlah;
                $this->harga = $pembelian->Total_Harga / max(1, $pembelian->Jumlah);
            }
        }
    }

    /**
     * Menghitung ulang total harga saat jumlah diubah.
     */
    public function updatedJumlah()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->totalHarga = ((float)$this->jumlah) * ((float)$this->harga);
    }
    
    /**
     * Menyimpan perubahan data ke database.
     */
    public function update()
    {
        if ($this->stok !== null && (int)$this->jumlah > $this->stok) {
            $this->addError('jumlah', 'Jumlah retur melebihi stok yang tersedia ('.$this->stok.').');
            return;
        }

        $this->retur->update([
            'Kode_Item' => (int)$this->kodeItem,
            'Nama_Item' => $this->namaItem,
            'Jenis' => $this->jenis,
            'Jumlah' => (int)$this->jumlah,
            'Satuan' => $this->satuan,
            'Harga' => $this->harga, // Menyimpan harga satuan
            'Total_Harga' => $this->totalHarga,
            'Bulan' => strtoupper($this->bulan),
            'Tahun' => (int)$this->tahun,
        ]);

        session()->flash('success', 'Data retur berhasil diperbarui.');
        return redirect()->route('pengembalian.index');
    }

    public function cancel()
    {
        return redirect()->route('pengembalian.index');
    }
};
?>

<div> <!-- <-- ROOT TAG PEMBUKA -->
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Data Retur /</span> Edit Data
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Form Edit Data Retur</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="update">
                <div class="mb-3">
                    <label for="kodeItem" class="form-label">Kode Item</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="kodeItem" wire:model.defer="kodeItem" placeholder="Contoh: 101">
                        <button type="button" class="btn btn-outline-primary" wire:click="searchItem">
                            <i class="bx bx-search"></i> Cari
                        </button>
                    </div>
                    
                    @if ($stok !== null)
                        <p class="mt-2 text-success fw-bold">Stok Tersedia: {{ number_format($stok, 0, ',', '.') }}</p>
                    @elseif (!empty($kodeItem))
                        <p class="mt-2 text-warning">Stok tidak ditemukan di data Pembelian.</p>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="namaItem" class="form-label">Nama Item</label>
                    <input type="text" class="form-control" id="namaItem" wire:model="namaItem" readonly>
                </div>
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <input type="text" class="form-control" id="jenis" wire:model="jenis" readonly>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jumlah" class="form-label">Jumlah Diretur</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" wire:model.live="jumlah">
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control" id="satuan" wire:model="satuan" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="totalHarga" class="form-label">Total Harga Retur (Rp)</label>
                    <input type="text" class="form-control" id="totalHarga" wire:model="totalHarga" readonly>
                </div>
                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <input type="text" class="form-control" id="bulan" wire:model="bulan">
                </div>
                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" class="form-control" id="tahun" wire:model="tahun">
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" wire:click="cancel" class="btn btn-secondary me-2">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>Update</span>
                        <span wire:loading>Memperbarui...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> <!-- <-- ROOT TAG PENUTUP -->