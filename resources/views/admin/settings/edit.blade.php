<x-layouts.admin title="Profil Sistem">
    <div class="settings-page-grid">
        <x-admin.panel>
            <div class="settings-panel-heading">
                <div>
                    <p class="section-kicker">Identitas Aplikasi</p>
                    <p>Informasi ini tampil pada area administrasi dan halaman autentikasi.</p>
                </div>
            </div>

            <form class="settings-form" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="settings-logo-field">
                    <div class="settings-logo-preview">
                        @if (! empty($settings['logo']))
                            <img src="{{ str_starts_with($settings['logo'], ['http://', 'https://', '/']) ? $settings['logo'] : asset('storage/'.$settings['logo']) }}" alt="Logo sistem">
                        @else
                            <img src="{{ asset('images/lais-logo-white.png') }}" alt="Logo sistem">
                        @endif
                    </div>
                    <div>
                        <label for="logo">Logo Sistem</label>
                        <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp">
                        <p>PNG, JPG, atau WEBP. Maksimal 2 MB.</p>
                        @error('logo')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="system_name">Nama Sistem <span>*</span></label>
                        <input id="system_name" name="system_name" value="{{ old('system_name', $settings['system_name'] ?? 'Sistem Penilaian Driver') }}" required>
                        @error('system_name')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-field">
                        <label for="support_contact">Kontak Bantuan</label>
                        <input id="support_contact" name="support_contact" value="{{ old('support_contact', $settings['support_contact'] ?? '') }}" placeholder="Contoh: admin@perusahaan.com">
                        @error('support_contact')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-field form-field-full">
                        <label for="copyright_text">Teks Copyright</label>
                        <input id="copyright_text" name="copyright_text" value="{{ old('copyright_text', $settings['copyright_text'] ?? '© '.now()->year.'. Seluruh hak dilindungi.') }}">
                        @error('copyright_text')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-actions"><button class="primary-button" type="submit"><x-lucide-save aria-hidden="true" /><span>Simpan Perubahan</span></button></div>
            </form>
        </x-admin.panel>

        <aside class="settings-help-card">
            <span class="settings-help-icon"><x-lucide-info aria-hidden="true" /></span>
            <h2>Catatan</h2>
            <p>Gunakan nama dan logo resmi agar tampilan sistem konsisten bagi administrator.</p>
            <p>Kontak bantuan ditujukan untuk informasi yang ditampilkan kepada pengguna internal.</p>
        </aside>
    </div>
</x-layouts.admin>
