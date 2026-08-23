<x-layouts.guest title="Login Admin">
    <main class="lais-auth-page">
        <div class="lais-auth-frame">
            <section class="lais-auth-shell">
                <aside class="lais-auth-brand" aria-label="Sistem Penilaian Driver">
                <div class="lais-auth-brand-top">
                    <img class="lais-auth-logo" src="{{ asset('images/lais-logo-white.png') }}" alt="Sistem Penilaian Driver">
                </div>

                <div class="lais-auth-brand-content">
                    <h2>Sistem<br>Penilaian Driver</h2>
                    <p>Pantau, nilai, dan tingkatkan performa driver Anda dengan lebih mudah.</p>
                </div>
                <img class="lais-auth-vehicle-art" src="{{ asset('images/lais-login-car.png') }}" alt="">
                <small>&copy; {{ now()->year }} Sistem Penilaian Driver. Semua hak dilindungi.</small>
                </aside>

                <section class="lais-auth-form-area" aria-labelledby="login-title">
                    <div class="lais-auth-card">
                    <header>
                        <h1 id="login-title">Masuk ke Sistem</h1>
                        <p>Gunakan akun Anda untuk melanjutkan.</p>
                    </header>

                    <form class="lais-auth-form" method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <label>
                            <span>Email</span>
                            <div class="lais-auth-input-wrap">
                                <input @class(['is-invalid' => $errors->any()]) name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="contoh@email.com" required autofocus>
                                <x-lucide-mail aria-hidden="true" />
                            </div>
                            @if ($errors->any())
                                <small class="lais-auth-error">{{ $errors->first('email') ?: $errors->first('password') }}</small>
                            @endif
                        </label>

                        <label>
                            <span>Kata sandi</span>
                            <div class="lais-auth-password-wrap">
                                <input name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required data-password-input>
                                <button class="lais-auth-password-toggle" type="button" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                                    <x-lucide-eye-off data-password-icon="hidden" aria-hidden="true" />
                                    <x-lucide-eye data-password-icon="visible" aria-hidden="true" />
                                </button>
                            </div>
                        </label>

                        <div class="lais-auth-options">
                            <label class="lais-auth-remember">
                                <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                <span>Ingatkan saya</span>
                            </label>
                            <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
                        </div>

                        <button type="submit"><span>Login</span></button>
                    </form>
                    </div>
                </section>
            </section>
        </div>
    </main>
</x-layouts.guest>
