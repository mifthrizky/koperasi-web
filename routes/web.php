<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
  return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
  ->middleware(['auth', 'verified'])
  ->name('dashboard');

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');

  // === ROUTE SIDEBAR ===
  // Route pembelian
  Volt::route('data/pembelian', 'data.pembelian.index')->name('pembelian.index');
  Volt::route('data/pembelian/tambah', 'data.pembelian.create')->name('pembelian.create');
  Volt::route('data/pembelian/{pembelian}/edit', 'data.pembelian.edit')->name('pembelian.edit');




  Volt::route('data/penjualan', 'data.penjualan.index')->name('penjualan.index');
  Volt::route('data/pengembalian', 'data.pengembalian.index')->name('pengembalian.index');
  Volt::route('data/stock-opname', 'data.stock-opname.index')->name('stock-opname.index');
  // ======================

});

require __DIR__ . '/auth.php';
