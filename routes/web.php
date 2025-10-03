<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'dashboard')
  ->middleware(['auth', 'verified'])
  ->name('dashboard');

Route::view('dashboard', 'dashboard')
  ->middleware(['auth', 'verified'])
  ->name('dashboard');

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');

  // === ROUTE SIDEBAR ===
  // Route pembelian
  Volt::route('data/barang', 'data.barang.index')->name('barang.index');
  Volt::route('data/barang/tambah', 'data.barang.create')->name('barang.create');
  Volt::route('data/barang/{barang}/edit', 'data.barang.edit')->name('barang.edit');

  Volt::route('data/pembelian', 'data.pembelian.index')->name('pembelian.index');
  Volt::route('data/pembelian/tambah', 'data.pembelian.create')->name('pembelian.create');
  Volt::route('data/pembelian/{pembelian}/edit', 'data.pembelian.edit')->name('pembelian.edit');

  Volt::route('data/pengembalian', 'data.pengembalian.index')->name('pengembalian.index');
  Volt::route('data/pengembalian/tambah', 'data.pengembalian.create')->name('pengembalian.create');
  Volt::route('data/pengembalian/{retur}/edit', 'data.pengembalian.edit')->name('retur.edit');

  Volt::route('data/penjualan', 'data.penjualan.index')->name('penjualan.index');
  Volt::route('data/penjualan/tambah', 'data.penjualan.create')->name('penjualan.create');
  Volt::route('data/penjualan/{penjualan}/edit', 'data.penjualan.edit')->name('penjualan.edit');

  Volt::route('data/pengembalian', 'data.pengembalian.index')->name('pengembalian.index');
  Volt::route('data/pengembalian', 'data.pengembalian.index')->name('pengembalian.index');
  Volt::route('data/pengembalian/tambah', 'data.pengembalian.create')->name('pengembalian.create');
  Volt::route('data/pengembalian/{retur}/edit', 'data.pengembalian.edit')->name('retur.edit');

  Volt::route('data/stock-opname', 'data.stock-opname.index')->name('stock-opname.index');
  // ======================

  Volt::route('data/barang/import', 'data.barang.import')->name('barang.import');
  Volt::route('data/pembelian/import', 'data.pembelian.import')->name('pembelian.import');
  Volt::route('data/penjualan/import', 'data.penjualan.import')->name('penjualan.import');
  Volt::route('data/pengembalian/import', 'data.pengembalian.import')->name('pengembalian.import');
});

require __DIR__ . '/auth.php';
