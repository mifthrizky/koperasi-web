<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
};
?>
@section('title', 'Halaman Login Koperasi Pegawai')

@section('page-style')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

<div class="login-container">
    <div class="login-form-wrapper">
        <div class="text-center mb-6">
            <img src="{{ asset('assets/img/logo/kopeg.png') }}" alt="Logo Koperasi Pegawai" style="max-width: 150px;">
            <h3 class="mb-0">Login <span class="text-primary">Koperasi Pegawai</span></h3>
        </div>

        @if (session('status'))
        <div class="alert alert-info mb-4">
            {{ session('status') }}
        </div>
        @endif

        <form wire:submit="login" class="mb-6">
            <div class="mb-4"> {{-- Margin disesuaikan --}}
                <label for="email" class="form-label">Email / Username</label>
                <input
                    wire:model="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="Email / Username">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4 form-password-toggle"> {{-- Margin disesuaikan --}}
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Password</label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-muted">
                        <span>Lupa Kata Sandi?</span>
                    </a>
                    @endif
                </div>
                <div class="input-group input-group-merge">
                    <input
                        wire:model="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        required
                        autocomplete="current-password"
                        placeholder="">
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Remember Me dikembalikan di sini --}}
            <div class="mb-6"> {{-- Margin disesuaikan --}}
                <div class="form-check">
                    <input wire:model="remember" type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <button type="submit" class="btn btn-primary d-grid w-100">Login</button>
            </div>
        </form>

        <p class="text-center mt-4 text-muted"> atau </p>

        @if (Route::has('register'))
        <p class="text-center">
            <a href="{{ route('register') }}" wire:navigate>
                <span>Daftar Anggota Baru</span>
            </a>
        </p>
        @endif
    </div>
</div>