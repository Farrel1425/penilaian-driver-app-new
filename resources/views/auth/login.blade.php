<x-layouts.guest title="Login Admin">
    @php
        $systemName = App\Models\SystemSetting::systemName();
        $systemLogoUrl = App\Models\SystemSetting::logoUrl();
        $supportContact = App\Models\SystemSetting::supportContact();
        $supportContactUrl = App\Models\SystemSetting::supportContactUrl();
        $copyrightText = App\Models\SystemSetting::copyrightText();
    @endphp
    <main class="bpd-login-page">
        <header class="bpd-login-topbar">
            <a class="bpd-login-brand" href="{{ route('home') }}" aria-label="{{ $systemName }}">
                <img src="{{ $systemLogoUrl }}" alt="Logo {{ $systemName }}">
                <span class="bpd-login-brand-copy"><strong>{{ $systemName }}</strong><span class="bpd-login-support"><i>Supported by</i><b>Bank BPD Bali</b></span></span>
            </a>
            <div class="bpd-login-help">
                @if ($supportContactUrl)
                    <a href="{{ $supportContactUrl }}" aria-label="Hubungi bantuan melalui {{ $supportContact }}" title="Hubungi bantuan: {{ $supportContact }}" @if (str_starts_with($supportContactUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif><x-lucide-circle-help aria-hidden="true" /></a>
                @endif
                <a href="{{ route('home') }}" aria-label="Informasi aplikasi" title="Informasi aplikasi"><x-lucide-info aria-hidden="true" /></a>
            </div>
        </header>

        <section class="bpd-login-hero bpd-login-hero-copy" aria-label="Aplikasi Penilaian Driver">
                <h1>Aplikasi <em>Penilaian</em><br>Driver.</h1>
                <p>Aplikasi Penilaian Driver adalah platform digital untuk memantau, mengukur, dan meningkatkan kinerja serta keselamatan berkendara para pengemudi secara transparan. Integrasi langsung dengan Bank BPD Bali mempermudah pencairan gaji, bonus, dan insentif secara real-time ke rekening driver.</p>
                <div class="bpd-login-metrics" aria-label="Keunggulan sistem">
                    <span><strong>24/7</strong><small>REAL-TIME TRACKING</small></span>
                    <span><strong>99.9%</strong><small>SYSTEM UPTIME</small></span>
                </div>        </section>

        <section class="bpd-login-card" aria-labelledby="login-title">
            <header>
                <h1 id="login-title">Selamat Datang</h1>
                <p>Aplikasi Penilaian Driver Supported by <strong>Bank BPD Bali</strong></p>
            </header>

            <form class="bpd-login-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <label>
                    <span>Identitas Pengguna</span>
                    <div class="bpd-login-input">
                        <x-lucide-user-round aria-hidden="true" />
                        <input @class(['is-invalid' => $errors->any()]) name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="Username atau Email" required autofocus>
                    </div>
                    @if ($errors->any())
                        <small class="bpd-login-error">{{ $errors->first('email') ?: $errors->first('password') }}</small>
                    @endif
                </label>

                <label>
                    <span>Kunci Akses</span>
                    <div class="bpd-login-input bpd-login-password">
                        <x-lucide-key-round aria-hidden="true" />
                        <input name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required data-password-input>
                        <button type="button" aria-label="Tampilkan kata sandi" aria-pressed="false" data-password-toggle>
                            <x-lucide-eye-off data-password-icon="hidden" aria-hidden="true" />
                            <x-lucide-eye data-password-icon="visible" aria-hidden="true" />
                        </button>
                    </div>
                </label>

                <div class="bpd-login-options">
                    <label>
                        <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                        <span>Tetap Masuk</span>
                    </label>
                </div>

                <button class="bpd-login-submit" type="submit"><span>Masuk Sistem</span><x-lucide-zap aria-hidden="true" /></button>
            </form>

            <footer>Lupa Kata Sandi? <a href="{{ route('password.request') }}">Reset Kata Sandi</a></footer>
        </section>

        <footer class="bpd-login-footer">
            @if ($copyrightText)<span>{{ $copyrightText }}</span>@endif
            <span class="bpd-login-legal">Privasi &nbsp;&nbsp; Syarat &amp; Ketentuan &nbsp;&nbsp; Arsitektur Keamanan</span>
        </footer>
    </main>
</x-layouts.guest>
