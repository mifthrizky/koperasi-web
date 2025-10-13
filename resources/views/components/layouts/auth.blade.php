<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">

<head>
  @include('partials.head')
  <style>
    /* Gaya dasar untuk memastikan layout full height */
    html,
    body {
      height: 100%;
      margin: 0;
      overflow: hidden;
      /* Mencegah scroll yang tidak diinginkan */
    }

    .authentication-wrapper {
      height: 100%;
      /* Pastikan wrapper mengambil tinggi penuh */
      display: flex;
      /* Memastikan flexbox berfungsi */
      align-items: center;
      /* Tengahkan vertikal jika diperlukan */
    }

    .authentication-inner {
      height: 100%;
      /* Pastikan inner juga mengambil tinggi penuh */
      width: 100%;
      /* Pastikan inner mengambil lebar penuh */
    }

    /* Gaya khusus untuk latar belakang ungu di sisi kiri */
    .auth-cover-bg-color {
      background-color: #6a4c93;
      /* Warna ungu solid */
      display: flex;
      /* Menggunakan flexbox untuk menengahkan gambar */
      justify-content: center;
      align-items: center;
    }

    /* Menyesuaikan tinggi card form login agar memenuhi tinggi parent */
    .login-form-card {
      height: 100vh;
      /* Mengambil tinggi penuh viewport */
      display: flex;
      align-items: center;
      /* Tengahkan konten form secara vertikal */
      border: none !important;
      /* Hapus border card jika ada */
      box-shadow: none !important;
      /* Hapus shadow card jika ada */
    }
  </style>
  @yield('page-style') {{-- Ini sangat penting untuk memuat CSS dari Livewire component --}}
</head>

<body class="h-100"> {{-- Hapus overflow-hidden dari body, sudah diatur di html,body --}}
  <x-layouts.auth.split>
    {{ $slot }}
  </x-layouts.auth.split>

  @include('partials.scripts')
</body>

</html>