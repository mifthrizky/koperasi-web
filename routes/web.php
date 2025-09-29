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
  Volt::route('data/pembelian', 'data.pembelian')->name('data.pembelian');
  Volt::route('data/penjualan', 'data.penjualan')->name('data.penjualan');
  Volt::route('data/pengembalian', 'data.pengembalian')->name('data.pengembalian');
  Volt::route('data/stock-opname', 'data.stock-opname')->name('data.stock-opname');
  // ======================
});

require __DIR__ . '/auth.php';
