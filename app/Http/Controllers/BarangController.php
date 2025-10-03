<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BarangsImport;
use App\Models\Barang; // Pastikan model Barang di-import
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BarangController extends Controller
{
    /**
     * Menampilkan halaman daftar barang (jika Anda membutuhkannya di controller ini).
     */
    public function index()
    {
        // Mungkin Anda sudah menanganinya dengan Livewire,
        // jadi metode ini bisa dikosongkan atau digunakan sesuai kebutuhan.
        return view('barang.index'); // Sesuaikan dengan nama view Anda
    }

    /**
     * Menampilkan form untuk upload file.
     */
    public function showImportForm()
    {
        return view('barang.import'); // View ini akan kita buat di langkah 5
    }

    /**
     * Menangani proses import file Excel.
     */
    public function handleImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new BarangsImport, $request->file('file'));

            return redirect()->route('barang.index')
                ->with('success', 'Data barang berhasil diimpor!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            // Anda bisa melakukan iterasi pada $failures untuk pesan error yang lebih spesifik
            return redirect()->back()
                ->with('error', 'Gagal mengimpor data. Pastikan format file Excel sudah benar.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menangani download file template Excel.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        // Pastikan Anda sudah membuat file template ini di direktori public
        $filePath = public_path('templates/template_barang.xlsx');

        if (!file_exists($filePath)) {
            // Jika file tidak ada, kembalikan response 404
            abort(404, 'File template tidak ditemukan.');
        }

        return response()->download($filePath);
    }
}
