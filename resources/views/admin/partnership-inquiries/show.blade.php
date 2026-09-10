<x-layouts.admin title="Detail Permintaan Kerjasama">
    <x-slot:pageActions><div class="resource-page-navigation">
        <a class="primary-button" href="{{ route('admin.partnership-inquiries.index') }}">
            <x-lucide-arrow-left aria-hidden="true" />
            <span>Kembali</span>
        </a>
    </div></x-slot>

    <div class="detail-layout inquiry-detail-layout">
        <x-admin.panel title="Informasi Calon Mitra">
            <div class="detail-grid">
                <x-admin.detail-row label="Perusahaan / Instansi" :value="$inquiry->company_name" />
                <x-admin.detail-row label="Nama PIC &amp; Jabatan" :value="$inquiry->contact_name" />
                <x-admin.detail-row label="Nomor WhatsApp" :value="$inquiry->whatsapp" />
                <x-admin.detail-row label="Email Resmi" :value="$inquiry->email" />
                <x-admin.detail-row label="Kebutuhan Layanan" :value="$inquiry->service" />
                <x-admin.detail-row label="Estimasi Kebutuhan" :value="$inquiry->estimated_need" />
                <x-admin.detail-row label="Durasi Kontrak" :value="$inquiry->contract_duration" />
                <x-admin.detail-row label="Dikirim Pada" :value="$inquiry->created_at->timezone(config('app.display_timezone'))->format('d M Y, H:i')" />
            </div>
            <div class="inquiry-notes">
                <span>KETERANGAN TAMBAHAN</span>
                <p>{{ $inquiry->notes ?: 'Tidak ada keterangan tambahan.' }}</p>
            </div>
        </x-admin.panel>

        <div class="record-detail-side">
            <x-admin.panel title="Status Permintaan">
                <span class="inquiry-status inquiry-status-{{ $inquiry->status }}">{{ $inquiry->statusLabel() }}</span>
                <form class="inquiry-status-form" method="POST" action="{{ route('admin.partnership-inquiries.status', $inquiry) }}">
                    @csrf
                    @method('PATCH')
                    <label for="inquiry-status">Ubah Status</label>
                    <select id="inquiry-status" name="status">
                        @foreach (\App\Models\PartnershipInquiry::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="primary-button" type="submit"><x-lucide-save aria-hidden="true" /><span>Simpan Status</span></button>
                </form>
            </x-admin.panel>
            <x-admin.panel title="Hubungi Calon Mitra">
                <div class="record-actions inquiry-contact-actions">
                    <a class="primary-button" href="mailto:{{ $inquiry->email }}"><x-lucide-mail aria-hidden="true" /><span>Kirim Email</span></a>
                    <a class="secondary-button" href="https://wa.me/{{ $inquiry->whatsappNumber() }}" target="_blank" rel="noopener"><x-lucide-message-circle aria-hidden="true" /><span>Buka WhatsApp</span></a>
                </div>
            </x-admin.panel>
        </div>
    </div>
</x-layouts.admin>
