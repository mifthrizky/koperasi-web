<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-base-url="{{url('/')}}" data-framework="laravel">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi Mahasiswa Polman Bandung</title>
    
    @include('partials.head')

    <style>
      /* --- GLOBAL STYLES --- */
      body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #fff;
        overflow: hidden; /* Penting untuk clip-path */
      }

      /* --- BACKGROUND GRADIENT DENGAN CLIP-PATH --- */
      .background-gradient {
        position: fixed; /* Menjadikan ini latar belakang tetap */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1; /* Menempatkan di belakang konten lain */
        
        /* WARNA GRADASI BIRU */
        background: #8400f8ff;
        background: -webkit-linear-gradient(to bottom right, #9100f1ff, #00d4ff);
        background: linear-gradient(to bottom right, #7a00c0ff, #00d4ff);

        /* PROPERTI KUNCI UNTUK BENTUK DIAGONAL */
        /* Sesuaikan koordinat sesuai keinginan. Di sini memotong dari kiri atas ke kanan bawah */
        clip-path: polygon(0 0, 100% 0, 100% 80%, 0% 100%); 
        /* Alternatif lain untuk potongan yang Anda tunjukkan: 
           clip-path: polygon(0 0, 100% 0, 100% 60%, 40% 100%, 0 100%); 
           Coba ini jika yang di atas tidak sesuai */
      }

      /* --- NAVBAR (Tombol Login/Register) --- */
      .navbar {
        display: flex;
        justify-content: flex-end;
        padding: 20px;
        position: absolute; /* Posisikan secara absolut di atas background */
        top: 0;
        right: 0;
        z-index: 10;
      }

      .navbar a {
        margin-left: 10px;
        padding: 8px 18px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 500;
        /* Tambahkan efek agar tombol terlihat menonjol di atas gambar/gradient */
        background: rgba(255, 255, 255, 0.3); 
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
      }

      .btn-login {
        color: #fff; /* Ubah warna teks agar terlihat di atas background transparan */
      }

      .btn-register {
        background: #ff4081; /* Jaga warna asli register */
        color: #fff;
      }

      /* --- MAIN CONTENT AREA --- */
      .main-content {
        flex: 1;
        display: flex;
        justify-content: space-between; /* Untuk menempatkan card dan gambar secara terpisah */
        align-items: center; /* Menempatkan elemen secara vertikal di tengah */
        padding: 50px; /* Padding di sekitar konten utama */
        z-index: 5; /* Menempatkan di atas background tapi di bawah navbar */
        position: relative; /* Penting agar z-index bekerja */
        height: 100vh; /* Agar main-content mengisi seluruh tinggi layar */
      }

      /* --- GLASS CARD (Teks) --- */
      .glass-card {
        background: rgba(20, 20, 20, 0.15);
        border-radius: 15px;
        padding: 40px;
        width: 600px;
        max-width: 400px; /* Lebar maksimal untuk kartu teks */
        box-shadow: 0 10px 32px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .glass-card h1 {
        font-family: 'Courier New', monospace;
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #fff
      }

      .glass-card p {
        font-size: 16px;
        line-height: 1.6;
        color: #f0f0f0;
      }

      /* --- GAMBAR (Di sisi kanan) --- */
      .content-image {
        width: 60%; /* Mengambil 60% lebar sisa */
        height: 80vh; /* Tinggi gambar agar terlihat proporsional */
        background-image: url("{{ asset('/assets/img/market.jpg') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        /* Tambahkan efek blur jika diinginkan pada gambar di sini */
        /* filter: blur(5px); */ 
      }
      
      /* --- FOOTER --- */
      .footer {
        text-align: center;
        padding: 20px;
        font-size: 12px;
        opacity: 0.8;
        z-index: 5; /* Pastikan footer terlihat */
        position: absolute; /* Letakkan di bagian bawah layar */
        bottom: 0;
        width: 100%;
      }

      /* Responsive Adjustments */
      @media (max-width: 768px) {
        .main-content {
          flex-direction: column;
          text-align: center;
          padding: 20px;
        }

        .glass-card {
          max-width: 100%;
          margin-bottom: 30px;
        }

        .content-image {
          width: 100%;
          height: 50vh;
        }
      }
    </style>
</head>
<body>
    <div class="background-gradient"></div>

    <div class="navbar">
      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" class="btn-login">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="btn-login">Login</a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn-register">Register</a>
          @endif
        @endauth
      @endif
    </div>

    <div class="main-content">
        <div class="glass-card">
            <h1>Koperasi Mahasiswa Polman Bandung</h1>
            <p>
              Selamat datang di sistem informasi Koperasi Mahasiswa Polman Bandung.
              Daftar dan login untuk mengakses layanan koperasi secara digital dengan mudah.
            </p>
        </div>

        <div class="content-image">
            </div>
    </div>

    <div class="footer">
      © {{ date('Y') }} Koperasi Mahasiswa Polman Bandung
    </div>

    @include('partials.scripts')
</body>
</html>