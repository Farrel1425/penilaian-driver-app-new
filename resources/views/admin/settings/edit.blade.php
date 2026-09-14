<x-layouts.admin title="Profil Sistem">
    @php($logoUrl = App\Models\SystemSetting::logoUrl())
    <div class="settings-page-grid">
        <x-admin.panel>
            <div class="settings-panel-heading">
                <span class="settings-panel-icon"><x-lucide-badge-check aria-hidden="true" /></span>
                <div>
                    <p class="section-kicker">Identitas Aplikasi</p>
                    <p>Informasi ini tampil pada area administrasi dan halaman autentikasi.</p>
                </div>
            </div>

            <form class="settings-form" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="settings-logo-field" data-settings-logo-field>
                    <div class="settings-logo-preview">
                        <img src="{{ $logoUrl }}" alt="Logo sistem" data-settings-logo-preview>
                    </div>
                    <div class="settings-logo-copy">
                        <label for="logo">Logo Sistem</label>
                        <p>Digunakan pada sidebar admin, halaman login, dan alur penilaian penumpang.</p>
                        <div class="settings-logo-actions">
                            <label class="settings-logo-upload" for="logo"><x-lucide-upload aria-hidden="true" /><span>Ganti Logo</span></label>
                            <span class="settings-logo-file-name" data-settings-logo-file-name aria-live="polite">Belum ada file baru</span>
                        </div>
                        <input class="sr-only" id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" data-settings-logo-input>
                        <small>PNG, JPG, atau WEBP. Maksimal 2 MB.</small>
                        @error('logo')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="system_name">Nama Sistem <span>*</span></label>
                        <input id="system_name" name="system_name" value="{{ old('system_name', $settings['system_name'] ?? 'Sistem Penilaian Driver') }}" required>
                        <small class="settings-field-note">Tampil pada sidebar admin, login, dan halaman penilaian penumpang.</small>
                        @error('system_name')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-field">
                        <label for="support_contact">Kontak Bantuan</label>
                        <input id="support_contact" name="support_contact" value="{{ old('support_contact', $settings['support_contact'] ?? '') }}" placeholder="Contoh: admin@perusahaan.com">
                        <small class="settings-field-note">Disimpan sebagai kontak bantuan untuk pengguna internal.</small>
                        @error('support_contact')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-field form-field-full">
                        <label for="copyright_text">Teks Copyright</label>
                        <input id="copyright_text" name="copyright_text" value="{{ old('copyright_text', $settings['copyright_text'] ?? '© '.now()->year.'. Seluruh hak dilindungi.') }}">
                        <small class="settings-field-note">Digunakan sebagai identitas legal dan kepemilikan aplikasi.</small>
                        @error('copyright_text')<small class="form-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a class="secondary-button" href="{{ route('admin.dashboard') }}"><x-lucide-x aria-hidden="true" /><span>Batal</span></a>
                    <button class="primary-button" type="submit"><x-lucide-save aria-hidden="true" /><span>Simpan</span></button>
                </div>
            </form>
        </x-admin.panel>

        <aside class="settings-help-card">
            <span class="settings-help-icon"><x-lucide-info aria-hidden="true" /></span>
            <h2>Catatan</h2>
            <p>Gunakan identitas resmi agar tampilan aplikasi tetap konsisten di setiap halaman.</p>
            <ul>
                <li><x-lucide-circle-check aria-hidden="true" /><span>Gunakan logo dengan latar transparan dan proporsi yang jelas.</span></li>
                <li><x-lucide-circle-check aria-hidden="true" /><span>Pastikan nama sistem singkat dan mudah dikenali.</span></li>
                <li><x-lucide-circle-check aria-hidden="true" /><span>Periksa kembali perubahan sebelum disimpan.</span></li>
            </ul>
        </aside>
    </div>
</x-layouts.admin>
