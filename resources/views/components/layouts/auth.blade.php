<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentication' }} - Koperasi</title>

    {{-- Memuat style dasar dari tema Anda --}}
    @include('partials.head')

    <style>
      /* --- GLOBAL STYLES --- */
      body {
        margin: 0;
        min-height: 100vh;
        font-family: 'Public Sans', sans-serif, -apple-system, BlinkMacSystemFont, 'Segoe UI', Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        color: #fff;
        overflow: hidden; /* Penting untuk clip-path */
        background-color: #fff; /* Latar belakang putih jika gradien tidak menutupi */
      }

      .form-label {
        color: #ffffffff; // Warna label hitam/abu-abu gelap
        font-weight: 600; // Sedikit tebal
        margin-bottom: 0.5rem; // Spasi antara label dan input
      }

      .btn-primary {
        background-color: #ab31fdff !important; // Ungu
        border-color: #000000ff !important; // Border ungu
        color: #FFFFFF !important; // Teks putih
        font-weight: 600; // Tebal
        padding: 0.75rem 1.5rem; // Padding
        border-radius: 0.5rem; // Rounded corner
        font-size: 1.1rem; // Ukuran font
        transition: all 0.2s ease-in-out; // Transisi hover
  
        &:hover {
        background-color: #6A1EB8 !important; // Ungu lebih gelap saat hover
        border-color: #6A1EB8 !important;
        }
        &:focus {
        box-shadow: 0 0 0 0.25rem rgba(138, 43, 226, 0.4) !important;
        }
      }
      .text-uwoyy {
        color: #ffffffff; // Warna teks abu-abu
        margin-top: 1.5rem; // Spasi atas
  
        a {
        color: #ff004cff !important; // Warna link "Sign in instead" ungu
        font-weight: 600;
        &:hover {
        text-decoration: underline;
          } 
        }
      }

      /* --- BACKGROUND GRADIENT DENGAN CLIP-PATH --- */
      .background-gradient {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        
        /* WARNA GRADASI BIRU */
        background: #b3b3b3ff;
        background: -webkit-linear-gradient(to bottom right, #ac00e0ff, #00d4ff);
        background: linear-gradient(to bottom right, #9900e0ff, #00d4ff);

        /* BENTUK DIAGONAL */
        clip-path: polygon(0 0, 100% 0, 100% 80%, 0% 100%);
      }

      /* --- MAIN CONTENT AREA --- */
      .main-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 50px;
        z-index: 5;
        position: relative;
        height: 100vh;
      }

      /* --- KARTU KACA (Untuk menampung form) --- */
      .glass-card {
        background: rgba(133, 106, 255, 1);
        border-radius: 20px;
        padding: 40px;
        max-width: 450px; /* Sedikit lebih lebar untuk form */
        width: 100%;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff; /* Pastikan teks di dalam slot berwarna putih */
      }
      
      /* Mengubah warna teks default dari form agar terlihat */
      .glass-card .form-label,
      .glass-card .form-check-label,
      .glass-card .text-center {
        color: #ffffffff !important;
      }
      .glass-card .text-center a span {
        color: #ffffffff !important;
        font-weight: bold;
      }
      .glass-card h4 {
        color: #ffffffff !important;
      }
      .glass-card p {
        color: #e0e0e0 !important;
      }
      
      /* --- GAMBAR (Di sisi kanan) --- */
      .content-image {
        width: 60%;
        height: 80vh;
        background-image: url("{{ asset('/assets/img/market.jpg') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      }
      
      /* --- Responsive Adjustments --- */
      @media (max-width: 992px) {
        .content-image {
          display: none; /* Sembunyikan gambar di layar kecil */
        }
        .main-content {
          justify-content: center; /* Pusatkan kartu form */
        }
        .glass-card {
           margin: 0 20px;
        }
      }
    </style>
</head>
<body>

    <div class="background-gradient"></div>

    <div class="main-content">
        
        <div class="glass-card">
            {{-- 
              DI SINILAH KEAJAIBANNYA TERJADI:
              `{{ $slot }}` akan secara otomatis mengambil seluruh konten form
              dari file `register.blade.php` dan menampilkannya di sini.
            --}}
            {{ $slot }}
        </div>

        <div class="content-image"></div>
    </div>

    {{-- Memuat script dasar dari tema Anda --}}
    @include('partials.scripts')

</body>
</html>