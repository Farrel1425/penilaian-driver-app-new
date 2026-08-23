<x-layouts.guest title="Atur Ulang Kata Sandi">
    <main class="auth-support-page">
        <section class="auth-support-card" aria-labelledby="reset-password-title">
            <a class="auth-support-back" href="{{ route('login') }}"><span class="auth-support-back-icon"><x-lucide-arrow-left aria-hidden="true" /></span><span>Kembali ke login</span></a>
            <span class="auth-support-kicker">ADMINISTRATOR SISTEM</span>
            <h1 id="reset-password-title">Buat kata sandi baru</h1>
            <p>Gunakan minimal 8 karakter dan simpan kata sandi yang mudah Anda ingat namun sulit ditebak.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input name="token" type="hidden" value="{{ $token }}">

                <label>
                    <span>Email</span>
                    <input name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" placeholder="contoh@email.com" required autofocus>
                    @error('email')
                        <small class="auth-support-error">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Kata sandi baru</span>
                    <input name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                    @error('password')
                        <small class="auth-support-error">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Konfirmasi kata sandi</span>
                    <input name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi kata sandi baru" required>
                </label>

                <button type="submit">Simpan kata sandi baru</button>
            </form>
        </section>
    </main>
</x-layouts.guest>
