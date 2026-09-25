<x-layouts.admin title="Recruitment">
    <x-slot:pageDescription>Kelola gelombang, lowongan, cabang penempatan, dan proses pelamar dalam satu halaman.</x-slot:pageDescription>
    <x-slot:pageActions>
        <div class="recruitment-heading-actions">
            <button class="secondary-button" type="button" data-period-create title="Tambah gelombang" aria-label="Tambah gelombang"><x-lucide-calendar-plus aria-hidden="true" /><span>Tambah</span></button>
            <button class="primary-button master-create-button" type="button" data-vacancy-create title="Tambah lowongan" aria-label="Tambah lowongan" @disabled($periods->isEmpty())><x-lucide-plus aria-hidden="true" /><span>Tambah</span></button>
        </div>
    </x-slot:pageActions>

    @if (session('error'))<div class="form-alert form-alert-error" role="alert">{{ session('error') }}</div>@endif

    <nav class="recruitment-tabs" aria-label="Bagian recruitment">
        <a @class(['is-active' => $activeTab === 'vacancies']) href="{{ route('admin.recruitment.index', ['tab' => 'vacancies']) }}"><x-lucide-briefcase-business aria-hidden="true" /><span>Lowongan &amp; Gelombang</span><b>{{ $periods->sum('vacancies_count') }}</b></a>
        <a @class(['is-active' => $activeTab === 'applicants']) href="{{ route('admin.recruitment.index', ['tab' => 'applicants']) }}"><x-lucide-users aria-hidden="true" /><span>Data Pelamar</span><b>{{ $applicationCount }}</b></a>
    </nav>

    @if ($activeTab === 'vacancies')
        <section class="recruitment-workspace">
            <header class="recruitment-workspace-toolbar">
                <div><h2>Gelombang dan Lowongan</h2><p>Lowongan dikelompokkan berdasarkan gelombang recruitment.</p></div>
                <form method="GET" action="{{ route('admin.recruitment.index') }}">
                    <input type="hidden" name="tab" value="vacancies">
                    <label><span>STATUS</span><select name="vacancy_status" onchange="this.form.requestSubmit()"><option value="">Semua Status</option>@foreach (App\Models\JobVacancy::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['vacancy_status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                    @if (filled($filters['search'] ?? null) || filled($filters['vacancy_status'] ?? null))<a class="secondary-button" href="{{ route('admin.recruitment.index') }}"><x-lucide-rotate-ccw aria-hidden="true" />Reset</a>@endif
                </form>
            </header>

            <div class="recruitment-period-list">
                @forelse ($periods as $period)
                    <details class="recruitment-period-group" @if ($period->is_active || filled($filters['search'] ?? null) || filled($filters['vacancy_status'] ?? null)) open @endif>
                        <summary>
                            <span class="recruitment-period-chevron"><x-lucide-chevron-right aria-hidden="true" /></span>
                            <span class="recruitment-period-summary-main"><strong>{{ $period->name }}</strong><small>Dibuat {{ $period->starts_at?->translatedFormat('d M Y') ?? 'tanggal belum tercatat' }}</small></span>
                            <form method="POST" action="{{ route('admin.recruitment-periods.active', $period) }}" class="recruitment-period-toggle-form" data-no-loading>
                                @csrf
                                @method('PATCH')
                                <label class="recruitment-period-switch" title="{{ $period->is_active ? 'Nonaktifkan gelombang' : 'Aktifkan gelombang' }}">
                                    <input type="checkbox" name="is_active" value="1" @checked($period->is_active) onchange="this.form.requestSubmit()" aria-label="{{ $period->is_active ? 'Nonaktifkan' : 'Aktifkan' }} gelombang {{ $period->name }}">
                                    <span class="recruitment-period-switch-track" aria-hidden="true"><i></i></span>
                                    <span>{{ $period->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </form>
                            <span class="recruitment-period-metric"><b>{{ $period->vacancies_count }}</b><small>Lowongan</small></span>
                            <span class="recruitment-period-metric"><b>{{ (int) $period->total_quota }}</b><small>Total Formasi</small></span>
                            <span class="recruitment-period-actions" onclick="event.stopPropagation();">
                                <button type="button" data-vacancy-create data-period-id="{{ $period->id }}" aria-label="Tambah lowongan pada gelombang {{ $period->name }}" title="Tambah lowongan"><x-lucide-plus aria-hidden="true" /></button>
                                <button type="button" data-period-edit="{{ $period->id }}" aria-label="Edit gelombang {{ $period->name }}" title="Edit gelombang"><x-lucide-pencil aria-hidden="true" /></button>
                                <form method="POST" action="{{ route('admin.recruitment-periods.destroy', $period) }}" data-delete-confirm data-no-loading data-delete-name="Gelombang {{ $period->name }}" data-delete-description="Gelombang yang sudah memiliki lowongan tidak dapat dihapus.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus gelombang {{ $period->name }}" title="Hapus gelombang"><x-lucide-trash-2 aria-hidden="true" /></button></form>
                            </span>
                        </summary>
                        <div class="recruitment-period-content">
                            <div class="table-wrap">
                                <table class="data-table recruitment-grouped-table">
                                    <thead><tr><th>POSISI</th><th>CABANG</th><th>FORMASI</th><th>PELAMAR</th><th>STATUS</th><th class="recruitment-action-cell">AKSI</th></tr></thead>
                                    <tbody>
                                        @forelse ($period->vacancies as $vacancy)
                                            <tr>
                                                <td><strong>{{ $vacancy->title }}</strong><small>{{ $vacancy->category }} &bull; {{ $vacancy->work_type }}</small></td>
                                                <td><span class="recruitment-branch-count" title="{{ $vacancy->branches->pluck('name')->join(', ') }}">{{ $vacancy->branches->count() }} Cabang</span><small>{{ $vacancy->branches->pluck('name')->take(2)->join(', ') }}@if($vacancy->branches->count() > 2) +{{ $vacancy->branches->count() - 2 }} lainnya @endif</small></td>
                                                <td><strong>{{ $vacancy->quota }}</strong><small>Personel</small></td>
                                                <td><strong>{{ $vacancy->applications_count }}</strong><small>Pelamar</small></td>
                                                <td><form method="POST" action="{{ route('admin.job-vacancies.active', $vacancy) }}" class="recruitment-vacancy-toggle-form" data-no-loading>@csrf @method('PATCH')<label class="recruitment-period-switch" title="{{ $vacancy->status === App\Models\JobVacancy::STATUS_OPEN ? 'Tutup lowongan' : 'Buka lowongan' }}"><input type="checkbox" name="is_active" value="1" @checked($vacancy->status === App\Models\JobVacancy::STATUS_OPEN) onchange="this.form.requestSubmit()" aria-label="{{ $vacancy->status === App\Models\JobVacancy::STATUS_OPEN ? 'Tutup' : 'Buka' }} lowongan {{ $vacancy->title }}"><span class="recruitment-period-switch-track" aria-hidden="true"><i></i></span><span>{{ $vacancy->status === App\Models\JobVacancy::STATUS_OPEN ? 'Aktif' : 'Nonaktif' }}</span></label></form></td>
                                                <td class="recruitment-action-cell"><div class="table-row-actions"><button type="button" data-vacancy-edit="{{ $vacancy->id }}" aria-label="Edit lowongan {{ $vacancy->title }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></button><form method="POST" action="{{ route('admin.job-vacancies.destroy', $vacancy) }}" data-delete-confirm data-no-loading data-delete-name="Lowongan {{ $vacancy->title }}" data-delete-description="Lowongan yang memiliki pelamar akan ditutup agar riwayat tetap aman.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus lowongan {{ $vacancy->title }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6"><div class="recruitment-period-empty"><x-lucide-briefcase aria-hidden="true" /><span><strong>{{ filled($filters['search'] ?? null) || filled($filters['vacancy_status'] ?? null) ? 'Tidak ada lowongan sesuai filter' : 'Belum ada lowongan pada gelombang ini' }}</strong><small>{{ filled($filters['search'] ?? null) || filled($filters['vacancy_status'] ?? null) ? 'Ubah pencarian atau status untuk melihat hasil lain.' : 'Tambahkan posisi dan cabang penempatannya.' }}</small></span>@unless(filled($filters['search'] ?? null) || filled($filters['vacancy_status'] ?? null))<button type="button" data-vacancy-create data-period-id="{{ $period->id }}">Tambah Lowongan</button>@endunless</div></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @empty
                    <x-admin.empty-state title="Belum ada gelombang recruitment" description="Buat gelombang pertama untuk mulai menambahkan lowongan." />
                @endforelse
            </div>
        </section>
    @else
        <section class="branch-list-card master-table-card recruitment-applicant-workspace">
            <header class="recruitment-workspace-toolbar">
                <div><h2>Data Pelamar</h2><p>Tinjau berkas dan tindak lanjut pelamar yang masuk.</p></div>
                <form method="GET" action="{{ route('admin.recruitment.index') }}">
                    <input type="hidden" name="tab" value="applicants"><input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <label><span>POSISI</span><select name="applicant_vacancy" onchange="this.form.requestSubmit()"><option value="">Semua Posisi</option>@foreach ($vacancies as $vacancy)<option value="{{ $vacancy->id }}" @selected((string) ($filters['applicant_vacancy'] ?? '') === (string) $vacancy->id)>{{ $vacancy->title }}</option>@endforeach</select></label>
                    <label><span>STATUS</span><select name="applicant_status" onchange="this.form.requestSubmit()"><option value="">Semua Status</option>@foreach (App\Models\JobApplication::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['applicant_status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                    @if (filled($filters['search'] ?? null) || filled($filters['applicant_vacancy'] ?? null) || filled($filters['applicant_status'] ?? null))<a class="secondary-button" href="{{ route('admin.recruitment.index', ['tab' => 'applicants']) }}"><x-lucide-rotate-ccw aria-hidden="true" />Reset</a>@endif
                </form>
            </header>
            <div class="table-wrap"><table class="data-table recruitment-admin-table"><thead><tr><th>TANGGAL</th><th>PELAMAR</th><th>POSISI &amp; GELOMBANG</th><th>CABANG PILIHAN</th><th>KONTAK</th><th>STATUS</th><th>AKSI</th></tr></thead><tbody>
                @forelse ($applications as $application)
                    <tr><td><strong>{{ $application->created_at->timezone(config('app.display_timezone'))->format('d M Y') }}</strong><small>{{ $application->created_at->timezone(config('app.display_timezone'))->format('H:i') }}</small></td><td><strong>{{ $application->full_name }}</strong><small>NIK {{ $application->maskedNik() }}</small></td><td><strong>{{ $application->vacancy->title }}</strong><small>{{ $application->vacancy->period->name }}</small></td><td>{{ $application->branch->name }}</td><td><span>{{ $application->whatsapp }}</span><small>{{ $application->email }}</small></td><td><span class="application-status application-status-{{ $application->status }}">{{ $application->statusLabel() }}</span></td><td><div class="table-row-actions"><button type="button" data-applicant-open="{{ $application->id }}" aria-label="Lihat pelamar {{ $application->full_name }}" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></button><a href="{{ route('admin.job-applications.document', $application) }}" download data-no-loading aria-label="Unduh PDF {{ $application->full_name }}" title="Unduh PDF"><x-lucide-download aria-hidden="true" /></a></div></td></tr>
                @empty<tr><td colspan="7"><x-admin.empty-state title="Belum ada pelamar" description="Lamaran yang dikirim dari halaman Recruitment akan muncul di sini." /></td></tr>@endforelse
            </tbody></table></div>
            <footer class="branch-pagination"><span>Menampilkan {{ $applications->firstItem() ?? 0 }} - {{ $applications->lastItem() ?? 0 }} dari {{ $applications->total() }} pelamar</span>@if ($applications->hasPages())<x-admin.pagination :paginator="$applications" label="Pagination pelamar" />@endif</footer>
        </section>
    @endif

    <dialog class="recruitment-admin-modal recruitment-period-modal" data-period-modal>
        <form method="POST" action="{{ route('admin.recruitment-periods.store') }}" data-period-form data-store-action="{{ route('admin.recruitment-periods.store') }}" data-update-action="{{ url('/admin/recruitment-periods/__ID__') }}">
            @csrf<input type="hidden" name="_method" value="PUT" disabled data-form-method><input type="hidden" name="form_context" value="period"><input type="hidden" name="record_id" value="" data-record-id>
            <header><span><x-lucide-calendar-days aria-hidden="true" /></span><div><h2 data-period-modal-title>Tambah Gelombang Recruitment</h2><p>Gelombang baru langsung aktif dan tanggal dibuat tercatat otomatis.</p></div><button type="button" data-modal-close aria-label="Tutup"><x-lucide-x aria-hidden="true" /></button></header>
            @if (old('form_context') === 'period' && $errors->any())<div class="form-alert form-alert-error"><strong>Periksa data gelombang:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="form-grid"><div class="form-field form-field-full"><label>Nama Gelombang <span>*</span></label><input name="name" required maxlength="150" placeholder="Contoh: Gelombang Recruitment Oktober 2026"></div></div>
            <footer><button class="secondary-button" type="button" data-modal-close>Batal</button><button class="primary-button" type="submit"><x-lucide-check aria-hidden="true" /><span data-period-submit-label>Buat Gelombang</span></button></footer>
        </form>
    </dialog>

    <dialog class="recruitment-admin-modal recruitment-vacancy-modal" data-vacancy-modal>
        <form method="POST" action="{{ route('admin.job-vacancies.store') }}" data-vacancy-form data-store-action="{{ route('admin.job-vacancies.store') }}" data-update-action="{{ url('/admin/job-vacancies/__ID__') }}">
            @csrf<input type="hidden" name="_method" value="PUT" disabled data-form-method><input type="hidden" name="form_context" value="vacancy"><input type="hidden" name="record_id" value="" data-record-id>
            <header><span><x-lucide-briefcase-business aria-hidden="true" /></span><div><h2 data-vacancy-modal-title>Tambah Lowongan</h2><p>Lengkapi kebutuhan posisi dan cabang penempatannya.</p></div><button type="button" data-modal-close aria-label="Tutup"><x-lucide-x aria-hidden="true" /></button></header>
            @if (old('form_context') === 'vacancy' && $errors->any())<div class="form-alert form-alert-error"><strong>Periksa data lowongan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="recruitment-vacancy-modal-body">
                <div class="form-grid recruitment-vacancy-form">
                    <div class="form-field form-field-full"><label>Gelombang Recruitment <span>*</span></label><select name="recruitment_period_id" required><option value="">Pilih gelombang</option>@foreach($periods as $period)<option value="{{ $period->id }}">{{ $period->name }}{{ $period->is_active ? ' (Aktif)' : '' }}</option>@endforeach</select><small>Lowongan baru otomatis aktif dan ditempatkan pada urutan terakhir gelombang ini.</small></div>
                    <div class="form-field"><label>Kategori Pekerjaan <span>*</span></label><input name="category" required maxlength="100" placeholder="Contoh: Armada & Driver"></div><div class="form-field"><label>Jenis Kontrak / Jadwal <span>*</span></label><input name="work_type" required maxlength="100" placeholder="Contoh: Penuh Waktu"></div>
                    <div class="form-field form-field-full"><label>Nama Posisi <span>*</span></label><input name="title" required maxlength="180"></div><div class="form-field form-field-full"><label>Deskripsi Pekerjaan <span>*</span></label><textarea name="description" rows="3" required></textarea></div><div class="form-field form-field-full"><label>Kualifikasi <span>*</span></label><textarea name="qualification" rows="2" required></textarea></div><div class="form-field form-field-full"><label>Gaji dan Fasilitas <span>*</span></label><textarea name="compensation" rows="2" required></textarea></div>
                    <div class="form-field"><label>Jumlah Formasi <span>*</span></label><input type="number" name="quota" min="1" required></div>
                </div>
                <fieldset class="recruitment-branch-picker"><legend>Cabang Penempatan <span>*</span></legend><p>Pilih satu atau beberapa cabang aktif.</p><div class="recruitment-branch-chips" data-branch-chips><span>Belum ada cabang dipilih</span></div><label class="recruitment-branch-search"><x-lucide-search aria-hidden="true" /><input type="search" placeholder="Cari nama, kode, atau wilayah cabang..." data-branch-search></label><div class="recruitment-branch-list" data-branch-list>@foreach($branches as $branch)<label data-branch-option data-search="{{ str($branch->name.' '.$branch->code.' '.$branch->regency)->lower() }}"><input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"><span><strong>{{ $branch->name }}</strong><small>{{ $branch->code }}@if($branch->regency) &bull; {{ $branch->regency }}@endif</small></span><x-lucide-check aria-hidden="true" /></label>@endforeach</div></fieldset>
            </div>
            <footer><button class="secondary-button" type="button" data-modal-close>Batal</button><button class="primary-button" type="submit"><x-lucide-check aria-hidden="true" /><span data-vacancy-submit-label>Buat Lowongan</span></button></footer>
        </form>
    </dialog>

    <dialog class="recruitment-admin-modal recruitment-applicant-modal" data-applicant-modal>
        <div>
            <header class="recruitment-applicant-dialog-header"><div><h2 data-applicant-name>Detail Pelamar</h2><p data-applicant-position></p></div><button type="button" data-modal-close aria-label="Tutup"><x-lucide-x aria-hidden="true" /></button></header>
            <div class="recruitment-applicant-detail-body"><div class="recruitment-applicant-detail-grid"><div><small>NIK</small><strong data-applicant-nik></strong></div><div><small>Dikirim pada</small><strong data-applicant-date></strong></div><div><small>WhatsApp</small><strong data-applicant-whatsapp></strong></div><div><small>Email</small><strong data-applicant-email></strong></div><div><small>Domisili</small><strong data-applicant-domicile></strong></div><div><small>Cabang Penempatan</small><strong data-applicant-branch></strong></div><div><small>Gelombang</small><strong data-applicant-period></strong></div><div><small>Berkas</small><strong data-applicant-document></strong></div></div><div class="recruitment-applicant-experience"><small>RINGKASAN PENGALAMAN</small><p data-applicant-experience></p></div></div>
            <footer class="recruitment-applicant-dialog-actions"><form method="POST" data-applicant-status-form>@csrf @method('PATCH')<select name="status" aria-label="Status pelamar">@foreach(App\Models\JobApplication::STATUSES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><button class="primary-button" type="submit"><x-lucide-save aria-hidden="true" />Simpan Status</button></form><a class="secondary-button" data-applicant-document-link download data-no-loading><x-lucide-download aria-hidden="true" />Unduh PDF</a><a class="secondary-button" data-applicant-whatsapp-link target="_blank" rel="noopener"><x-lucide-message-circle aria-hidden="true" />WhatsApp</a><a class="secondary-button" data-applicant-email-link><x-lucide-mail aria-hidden="true" />Email</a></footer>
        </div>
    </dialog>

    @php
        $periodPayload = $periods->map(fn ($period) => ['id' => $period->id, 'name' => $period->name, 'starts_at' => $period->starts_at?->format('Y-m-d'), 'ends_at' => $period->ends_at?->format('Y-m-d'), 'is_active' => $period->is_active]);
        $vacancyPayload = $periods->flatMap->vacancies->map(fn ($vacancy) => ['id' => $vacancy->id, 'recruitment_period_id' => $vacancy->recruitment_period_id, 'category' => $vacancy->category, 'work_type' => $vacancy->work_type, 'title' => $vacancy->title, 'description' => $vacancy->description, 'qualification' => $vacancy->qualification, 'compensation' => $vacancy->compensation, 'quota' => $vacancy->quota, 'sort_order' => $vacancy->sort_order, 'status' => $vacancy->status, 'branch_ids' => $vacancy->branches->pluck('id')]);
        $applicationPayload = $applications->map(fn ($application) => ['id' => $application->id, 'full_name' => $application->full_name, 'nik' => $application->nik, 'whatsapp' => $application->whatsapp, 'whatsapp_url' => 'https://wa.me/'.$application->whatsappNumber(), 'email' => $application->email, 'domicile' => $application->domicile, 'branch' => $application->branch->name, 'vacancy' => $application->vacancy->title, 'period' => $application->vacancy->period->name, 'experience' => $application->experience ?: 'Tidak ada ringkasan pengalaman.', 'status' => $application->status, 'status_label' => $application->statusLabel(), 'submitted_at' => $application->created_at->timezone(config('app.display_timezone'))->format('d M Y, H:i'), 'document_name' => $application->document_original_name, 'document_url' => route('admin.job-applications.document', $application), 'status_url' => route('admin.job-applications.status', $application)]);
    @endphp
    <script type="application/json" data-period-records>{!! $periodPayload->values()->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" data-vacancy-records>{!! $vacancyPayload->values()->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" data-applicant-records>{!! $applicationPayload->values()->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <span hidden data-recruitment-old-input data-context="{{ old('form_context') }}" data-record-id="{{ old('record_id') }}"></span>
    <script type="application/json" data-recruitment-old-values>{!! collect(old())->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-layouts.admin>
