<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">
    <!-- Bagian Kiri -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-0 auth-cover-bg-color">
      <div class="w-100 h-100">
        <img src="{{ asset('assets/img/illustrations/boy-kopeg.png') }}"
          class="w-100 h-100"
          alt="Login image"
          style="object-fit: cover;" />
      </div>
    </div>

    <!-- Bagian Kanan (Form Login) -->
    <div class="card col-12 col-lg-5 col-xl-4 login-form-card">
      <div class="d-flex align-items-center authentication-bg p-sm-12 p-6 h-100">
        <div class="w-px-400 mx-auto">
          {{ $slot }}
        </div>
      </div>
    </div>
  </div>
</div>