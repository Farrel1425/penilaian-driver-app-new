<x-layouts.guest title="Lupa Kata Sandi">
    <main class="auth-support-page">
        <section class="auth-support-card auth-support-card--request" aria-labelledby="forgot-password-title">
            <a class="auth-support-back" href="{{ route('login') }}" aria-label="Kembali ke login"><x-lucide-arrow-left aria-hidden="true" /></a>

            <div class="auth-support-intro">
                <div class="auth-support-lock" aria-hidden="true">
                    <span class="auth-support-lock-main"><x-lucide-lock-keyhole /></span>
                    <span class="auth-support-lock-check"><x-lucide-check /></span>
                </div>
                <h1 id="forgot-password-title">Atur ulang kata sandi</h1>
                <p>Masukkan email administrator Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
            </div>

            @if (session('status'))
                <p class="auth-support-status" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <label>
                    <span>Email</span>
                    <span class="auth-support-input-wrap">
                        <x-lucide-mail aria-hidden="true" />
                        <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="contoh@email.com" required autofocus>
                    </span>
                    @error('email')
                        <small class="auth-support-error">{{ $message }}</small>
                    @enderror
                </label>
                <button type="submit"><x-lucide-send aria-hidden="true" /><span>Kirim tautan reset</span></button>
            </form>
        </section>
    </main>
</x-layouts.guest>
