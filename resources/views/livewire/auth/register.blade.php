<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
};
?>

@section('title', 'Halaman Pendaftaran Anggota')

@section('page-style')
@vite([
'resources/assets/css/custom-login.css' {{-- gunakan css yang sama biar seragam --}}
])
@endsection

<div class="login-container">
    <div class="login-form-wrapper">
        <div class="text-center mb-6">
            <img src="{{ asset('assets/img/logo/kopeg.png') }}" alt="Logo Koperasi Pegawai" style="max-width: 150px;">
            <h3 class="mb-0">Daftar <span class="text-primary">Koperasi Pegawai</span></h3>
        </div>

        @if (session('status'))
        <div class="alert alert-info mb-4">
            {{ session('status') }}
        </div>
        @endif

        <form wire:submit="register" class="mb-6">

            <div class="mb-4">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input
                    wire:model="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Masukkan nama lengkap">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input
                    wire:model="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    required
                    autocomplete="email"
                    placeholder="Masukkan email aktif">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4 form-password-toggle">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="input-group input-group-merge">
                    <input
                        wire:model="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        required
                        autocomplete="new-password"
                        placeholder="********">
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4 form-password-toggle">
                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                <div class="input-group input-group-merge">
                    <input
                        wire:model="password_confirmation"
                        type="password"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="********">
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <div class="form-check">
                    <input wire:model="terms" type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" id="terms">
                    <label class="form-check-label" for="terms">
                        Saya setuju dengan <a href="javascript:void(0);">kebijakan privasi & ketentuan</a>
                    </label>
                    @error('terms')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <button type="submit" class="btn btn-primary d-grid w-100">Daftar</button>
            </div>
        </form>

        <p class="text-center mt-4 text-muted">Sudah punya akun?</p>
        <p class="text-center">
            <a href="{{ route('login') }}" wire:navigate>
                <span>Masuk di sini</span>
            </a>
        </p>
    </div>
</div>