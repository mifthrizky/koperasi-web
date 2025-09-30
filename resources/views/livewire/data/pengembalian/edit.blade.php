<?php

use Livewire\Volt\Component;
use App\Models\Pembelian;
use App\Models\Retur;

new class extends Component
{
    public Retur $retur;

    public string $kodeItem = '';
    public string $namaItem = '';
    public string $jumlah = '';
    public string $satuan = '';
    public float $harga = 0;
    public float $totalHarga = 0;
    public string $bulan = '';
    public string $tahun = '';

    // [BARU] Properti untuk jenis dan stok
    public string $jenis = '';
    public int $stok = 0;

    public function mount(Retur $retur)
    {
        $this->retur = $retur;

        $this->kodeItem = (string)$retur->Kode_Item;
        $this->namaItem = $retur->Nama_Item;
        $this->jumlah = (string)$retur->Jumlah;
        $this->satuan = $retur->Satuan;
        $this->harga = (float)$retur->Harga;
        $this->totalHarga = (float)$retur->Total_Harga;
        $this->bulan = $retur->Bulan;
        $this->tahun = (string)$retur->Tahun;

        // [BARU] Mengambil data jenis dan stok saat komponen dimuat
        $this->getDetailItem($this->kodeItem);
    }

    public function updatedKodeItem($value)
    {
        $this->getDetailItem($value);
    }
    
    // [BARU] Fungsi terpisah untuk mengambil detail item
    private function getDetailItem($kodeItem)
    {
        $pembelian = Pembelian::where('Kode_Item', $kodeItem)->first();

        if ($pembelian) {
            $this->namaItem = $pembelian->Nama_Item;
            $this->satuan = $pembelian->Satuan;
            $this->harga = $pembelian->Harga ?? 0;
            $this->jenis = $pembelian->Jenis ?? 'Tidak ada jenis'; // Ambil data jenis
            $this->stok = $pembelian->Stok ?? 0; // Ambil data stok
        } else {
            $this->namaItem = '';
            $this->satuan = '';
            $this->harga = 0;
            $this->jenis = '';
            $this->stok = 0;
        }
        $this->calculateTotal();
    }

    public function updatedJumlah()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->totalHarga = ((float)$this->jumlah) * ((float)$this->harga);
    }

    public function update()
    {
        $this->retur->update([
            'Kode_Item' => $this->kodeItem,
            'Nama_Item' => $this->namaItem,
            'Jumlah' => $this->jumlah,
            'Satuan' => $this->satuan,
            'Harga' => $this->harga,
            'Total_Harga' => $this->totalHarga,
            'Bulan' => $this->bulan,
            'Tahun' => $this->tahun,
            // 'Jenis' => $this->jenis, // Uncomment baris ini jika Anda juga menyimpan 'Jenis' di tabel Retur
        ]);

        session()->flash('success', 'Data retur berhasil diperbarui.');
        return redirect()->route('pengembalian.index');
    }

    // [BARU] Fungsi untuk tombol batal
    public function cancel()
    {
        return redirect()->route('pengembalian.index');
    }
};
?>

<div class="container mt-4">
    <h4>Edit Data Retur</h4>

    <form wire:submit.prevent="update">
        <div class="mb-3">
            <label for="kodeItem" class="form-label">Kode Item</label>
            <input type="text" wire:model.live="kodeItem" class="form-control">
        </div>

        <div class="mb-3">
            <label for="namaItem" class="form-label">Nama Item</label>
            <input type="text" wire:model="namaItem" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="jenis" class="form-label">Jenis Item</label>
            <input type="text" wire:model="jenis" class="form-control" readonly>
        </div>
        
        <div class="mb-3">
            <label for="stok" class="form-label">Stok Saat Ini</label>
            <input type="text" wire:model="stok" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" wire:model="satuan" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah</label>
            <input type="number" wire:model.live="jumlah" class="form-control">
        </div>

        <div class="mb-3">
            <label for="totalHarga" class="form-label">Total Harga</label>
            <input type="text" wire:model="totalHarga" class="form-control" readonly>
        </div>

        <div class="row">
            <div class="col">
                <label for="bulan" class="form-label">Bulan</label>
                <input type="text" wire:model="bulan" class="form-control">
            </div>
            <div class="col">
                <label for="tahun" class="form-label">Tahun</label>
                <input type="text" wire:model="tahun" class="form-control">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Update</button>
            <button type="button" wire:click="cancel" class="btn btn-secondary">Batal</button>
        </div>
    </form>
</div>