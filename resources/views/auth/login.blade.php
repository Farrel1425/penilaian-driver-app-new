<x-layouts.guest title="Login Admin">
    <main class="lais-auth-page">
        <aside class="lais-auth-brand" aria-label="LAIS">
            <div class="lais-auth-brand-content">
                <div class="lais-auth-logo-wrap">
                    <img class="lais-auth-logo" src="{{ asset('images/lais-logo-white.png') }}" alt="LAIS">
                </div>
                <p>Evaluasi kendaraan dan driver secara<br>mudah, akurat, dan terstruktur</p>
            </div>
            <small>&copy; {{ now()->year }}. Semua hak dilindungi.</small>
        </aside>

        <section class="lais-auth-form-area" aria-labelledby="login-title">
            <div class="lais-auth-card">
                <header>
                    <h1 id="login-title">Selamat Datang!</h1>
                    <p>Masuk menggunakan akun administrator</p>
                </header>

                <form class="lais-auth-form" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <label>
                        <span>Email</span>
                        <input @class(['is-invalid' => $errors->any()]) name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="nama@email.com" required autofocus>
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

                    <label class="lais-auth-remember">
                        <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                        <span>Ingat saya</span>
                    </label>

                    <button type="submit">Masuk</button>
                </form>

                {{-- <footer>Butuh bantuan? Hubungi administrator sistem.</footer> --}}
            </div>
        </section>
    </main>
</x-layouts.guest>
