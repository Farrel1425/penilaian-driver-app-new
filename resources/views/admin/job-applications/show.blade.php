<x-layouts.admin title="Detail Pelamar">
    <x-slot:pageActions><div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.job-applications.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div></x-slot:pageActions>
    <div class="detail-layout recruitment-application-detail">
        <x-admin.panel title="Informasi Pelamar">
            <div class="detail-grid">
                <x-admin.detail-row label="Nama Lengkap" :value="$application->full_name" /><x-admin.detail-row label="Nomor Induk Kependudukan" :value="$application->nik" />
                <x-admin.detail-row label="Nomor WhatsApp" :value="$application->whatsapp" /><x-admin.detail-row label="Email" :value="$application->email" />
                <x-admin.detail-row label="Domisili Saat Ini" :value="$application->domicile" /><x-admin.detail-row label="Cabang Penempatan" :value="$application->branch->name" />
                <x-admin.detail-row label="Posisi yang Dilamar" :value="$application->vacancy->title" /><x-admin.detail-row label="Periode" :value="$application->vacancy->period->name" />
                <x-admin.detail-row label="Dikirim Pada" :value="$application->created_at->timezone(config('app.display_timezone'))->format('d M Y, H:i')" /><x-admin.detail-row label="Persetujuan Data" :value="$application->consent_at->timezone(config('app.display_timezone'))->format('d M Y, H:i')" />
            </div>
            <div class="inquiry-notes"><span>RINGKASAN PENGALAMAN</span><p>{{ $application->experience ?: 'Tidak ada ringkasan pengalaman.' }}</p></div>
        </x-admin.panel>
        <div class="record-detail-side">
            <x-admin.panel title="Status Pelamar"><span class="application-status application-status-{{ $application->status }}">{{ $application->statusLabel() }}</span><form class="inquiry-status-form" method="POST" action="{{ route('admin.job-applications.status', $application) }}">@csrf @method('PATCH')<label for="application-status">Ubah Status</label><select id="application-status" name="status">@foreach (App\Models\JobApplication::STATUSES as $value => $label)<option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>@endforeach</select><button class="primary-button" type="submit"><x-lucide-save aria-hidden="true" /><span>Simpan Status</span></button></form></x-admin.panel>
            <x-admin.panel title="Berkas Lamaran"><p class="recruitment-document-name"><x-lucide-file-text aria-hidden="true" /><span>{{ $application->document_original_name }}</span></p><a class="primary-button recruitment-document-download" href="{{ route('admin.job-applications.document', $application) }}" download data-no-loading><x-lucide-download aria-hidden="true" /><span>Unduh Berkas PDF</span></a></x-admin.panel>
            <x-admin.panel title="Hubungi Pelamar"><div class="record-actions inquiry-contact-actions"><a class="primary-button" href="mailto:{{ $application->email }}"><x-lucide-mail aria-hidden="true" /><span>Kirim Email</span></a><a class="secondary-button" href="https://wa.me/{{ $application->whatsappNumber() }}" target="_blank" rel="noopener"><x-lucide-message-circle aria-hidden="true" /><span>Buka WhatsApp</span></a></div></x-admin.panel>
        </div>
    </div>
</x-layouts.admin>
