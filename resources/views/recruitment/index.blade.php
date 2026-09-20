<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Lowongan kerja resmi PT. Bali Dana Sejahtera. Proses recruitment transparan dan tanpa biaya.">
    <link rel="icon" type="image/png" href="{{ asset('images/bds/bds-logo.png') }}">
    <title>Recruitment | PT. Bali Dana Sejahtera</title>
    {!! Illuminate\Support\Facades\Vite::fonts() !!}
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body class="bds-landing recruitment-landing">
    @php
        $icon = fn ($number = '') => asset('images/bds/imgContainer'.$number.'.svg');
        $recruitmentIcon = fn (int $number) => asset('images/bds/recruitment/figma-icon-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.svg');
        $logo = asset('images/bds/bds-logo.png');
        $copyrightText = App\Models\SystemSetting::copyrightText();
        $oldVacancyId = (string) old('job_vacancy_id', '');
        $vacancyBranches = $vacancies->mapWithKeys(fn ($vacancy) => [
            (string) $vacancy->id => $vacancy->branches->map(fn ($branch) => ['id' => $branch->id, 'name' => $branch->name])->values(),
        ]);
    @endphp

    <header class="bds-header recruitment-header">
        <div class="bds-header-inner">
            <a class="bds-brand" href="{{ route('home') }}">
                <img src="{{ $logo }}" alt="Logo PT. Bali Dana Sejahtera" width="38" height="38">
                <span><strong>PT. Bali Dana Sejahtera</strong><small>Corporate Solutions &bull; Est. 2007</small></span>
            </a>
            <nav class="bds-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#beranda">Beranda</a>
                <a href="{{ route('home') }}#layanan">Layanan</a>
                <a href="{{ route('home') }}#profil">Profil &amp; Legalitas</a>
                <a href="{{ route('home') }}#mitra">Mitra Korporasi</a>
                <a href="{{ route('home') }}#sop">SOP Kualitas</a>
                <a href="{{ route('home') }}#hubungi">Hubungi</a>
                <a href="{{ route('recruitment.index') }}" data-sections="recruitment" aria-current="page">Recruitment</a>
            </nav>
            <a class="bds-button bds-login" href="{{ route('login') }}">Login <img src="{{ $icon(24) }}" alt="" width="9" height="9"></a>
        </div>
    </header>

    <main>
        <section id="recruitment" class="recruitment-page bds-shell">
            <div class="recruitment-heading">
                <div class="recruitment-heading-main">
                    <a class="recruitment-back" href="{{ route('home') }}" aria-label="Kembali ke beranda"><img src="{{ $recruitmentIcon(2) }}" alt=""></a>
                    <div>
                        <p class="recruitment-kicker">{{ $period?->name ?? 'FORMASI RECRUITMENT' }}</p>
                        <h1>Peluang Karir <em>Aktif &amp; Resmi</em></h1>
                    </div>
                </div>
                <p>Pilih bidang profesi sesuai keahlian dan kualifikasi Anda.<br>Seluruh lamaran diproses langsung oleh departemen HRD tanpa biaya perantara.</p>
            </div>

            @if (session('application_status'))
                <div class="recruitment-feedback is-success" role="status">
                    <strong>Lamaran berhasil dikirim</strong>
                    <span>{{ session('application_status') }}</span>
                </div>
            @endif

            <div class="recruitment-grid">
                @forelse ($vacancies as $vacancy)
                    <article class="job-card">
                        <div class="job-card-meta">
                            <span>{{ $vacancy->category }}</span>
                            <small><img src="{{ $recruitmentIcon(3) }}" alt="">{{ $vacancy->work_type }}</small>
                        </div>
                        <h2>{{ $vacancy->title }}</h2>
                        <p class="job-card-description">{{ $vacancy->description }}</p>
                        <div class="job-card-details">
                            <p><img src="{{ $recruitmentIcon(4) }}" alt=""><span>{{ $vacancy->qualification }}</span></p>
                            <p><img src="{{ $recruitmentIcon(6) }}" alt=""><span>{{ $vacancy->placementLabel() }}</span></p>
                            <p><img src="{{ $recruitmentIcon(9) }}" alt=""><span>{{ $vacancy->compensation }}</span></p>
                        </div>
                        <footer>
                            <strong>Formasi: {{ $vacancy->quota }} Personel</strong>
                            <button type="button" data-application-open="{{ $vacancy->id }}" aria-label="Lamar posisi {{ $vacancy->title }}">Lamar Posisi <img src="{{ $recruitmentIcon(17) }}" alt=""></button>
                        </footer>
                    </article>
                @empty
                    <div class="recruitment-empty">
                        <strong>Belum ada lowongan aktif</strong>
                        <p>Silakan kembali lagi pada periode recruitment berikutnya.</p>
                    </div>
                @endforelse
            </div>

            <aside class="recruitment-integrity">
                <span><img src="{{ $recruitmentIcon(8) }}" alt=""> PRAKATA MANAJEMEN &amp; DIREKSI</span>
                <blockquote>“Komitmen Rekrutmen Terbuka, Transparan,<br>dan <em>100% Bebas Biaya Pungutan</em>”</blockquote>
                <p>PT. Bali Dana Sejahtera menjamin setiap calon tenaga kerja dipilih murni atas dasar integritas,<br>kesepakatan kerja, dan kecakapan kompetensi. Tidak ada pungutan sepeserpun dalam bentuk apapun.</p>
            </aside>

            <div class="recruitment-process-heading">
                <p>ALUR PENERIMAAN BERKAS</p>
                <h2>Proses Seleksi Transparan, Cepat, &amp;<br><em>Tanpa Biaya</em></h2>
                <span>Kami memegang teguh standar profesionalisme. 4 tahapan ringkas dari pengiriman<br>berkas hingga resmi bertugas di instansi rekanan.</span>
            </div>
            <div class="recruitment-process-grid">
                <article><b>01</b><h3>Registrasi &amp; Berkas Online</h3><p>Pengisian data identitas lewat formulir web serta pengunggahan scan KTP, CV terbaru, dan sertifikasi berkaitan.</p><small><img src="{{ $recruitmentIcon(9) }}" alt=""> Waktu Proses: 1-2 Hari Kerja</small></article>
                <article><b>02</b><h3>Wawancara &amp; Uji Kualifikasi</h3><p>Wawancara bersama tim HRD serta tes kemampuan jasmani untuk personel operasional.</p><small><img src="{{ $recruitmentIcon(12) }}" alt=""> Undangan via WhatsApp Resmi</small></article>
                <article><b>03</b><h3>Verifikasi Medis &amp; Legalitas</h3><p>Pengecekan keaslian berkas, catatan kesehatan, dan latar belakang calon tenaga kerja.</p><small><img src="{{ $recruitmentIcon(14) }}" alt=""> Integritas Bebas Masalah Hukum</small></article>
                <article><b>04</b><h3>Induksi, PKWTT &amp; Penempatan</h3><p>Penandatanganan kontrak, pembagian seragam, briefing SOP perbankan, dan penyerahan ke lokasi tugas.</p><small><img src="{{ $recruitmentIcon(15) }}" alt=""> Siap Bertugas &amp; Hak Upah Aktif</small></article>
            </div>
        </section>
    </main>

    <footer class="bds-footer recruitment-footer">
        <div class="bds-shell">
            <div class="bds-footer-grid">
                <div class="bds-footer-brand"><a class="bds-brand" href="{{ route('home') }}"><img src="{{ $logo }}" alt="Logo PT. Bali Dana Sejahtera" width="38" height="38"><strong>PT. Bali Dana Sejahtera</strong></a><p>Perusahaan penyedia persewaan kendaraan operasional, tenaga alih daya tersertifikasi, pengadaan dan percetakan, serta layanan pembersihan dan perawatan AC di Bali sejak 2007.</p><small>SK Kemenkumham RI Terdaftar Resmi</small></div>
                <div><h3>LAYANAN UTAMA</h3><a href="{{ route('home') }}#layanan">Persewaan Armada Mobil Dinas</a><a href="{{ route('home') }}#layanan">Tenaga Alih Daya Terpadu</a><a href="{{ route('home') }}#layanan">Perdagangan &amp; Percetakan</a><a href="{{ route('home') }}#layanan">Perawatan AC</a></div>
                <div><h3>INFORMASI KORPORAT</h3><a href="{{ route('home') }}">Beranda</a><a href="{{ route('home') }}#profil">Profil &amp; Kepemilikan</a><a href="{{ route('home') }}#mitra">Mitra Korporasi</a><a href="{{ route('home') }}#sop">SOP &amp; Jaminan Kualitas</a><a href="{{ route('home') }}#hubungi">Hubungi Sekretariat</a></div>
                <div><h3>KEPATUHAN HUKUM</h3><a href="#">Disnaker Prov. Bali</a><a href="#">BPJS Ketenagakerjaan</a><a href="#">BPJS Kesehatan</a><a href="#">Standar K3 Operasional</a></div>
            </div>
            <div class="bds-footer-bottom"><span>{{ $copyrightText ?: '© '.now()->year.' PT. Bali Dana Sejahtera. Seluruh Hak Cipta Dilindungi.' }}</span><span class="bds-footer-location"><i></i> Denpasar, Bali</span></div>
        </div>
    </footer>

    <dialog class="recruitment-modal" data-application-modal @if ($errors->any()) data-application-auto-open @endif>
        <div class="recruitment-modal-card">
            <aside class="recruitment-modal-info">
                <div>
                    <p class="recruitment-help-label"><img src="{{ $recruitmentIcon(18) }}" alt=""> Pusat Informasi &amp; Bantuan HRD</p>
                    <h2>Kirim Berkas Lamaran <em>Resmi</em> Anda</h2>
                    <p>Lengkapi formulir secara cermat sesuai identitas kependudukan Anda. Data yang masuk akan diproses langsung oleh tim Talent Acquisition PT. BDS dalam 24-48 jam.</p>
                    <section>
                        <h3><img src="{{ $recruitmentIcon(19) }}" alt=""> DOKUMEN YANG WAJIB DISIAPKAN:</h3>
                        <ul>
                            <li>Scan KTP &amp; Kartu Keluarga (format PDF / Foto jelas)</li>
                            <li>Curriculum Vitae (CV) &amp; Riwayat Pengalaman Kerja</li>
                            <li>Ijazah Gada Pratama &amp; KTA (Khusus Satpam)</li>
                            <li>SIM A / B1 Aktif (Khusus Driver Operasional)</li>
                            <li>SKCK Kepolisian yang masih berlaku</li>
                        </ul>
                    </section>
                </div>
                <div class="recruitment-whatsapp">
                    <span><img src="{{ $recruitmentIcon(23) }}" alt=""></span>
                    <p>WhatsApp Layanan Recruitment<strong>{{ $recruitmentWhatsapp ?: 'Belum diatur' }}</strong></p>
                    @if ($recruitmentWhatsappUrl)<a href="{{ $recruitmentWhatsappUrl }}" target="_blank" rel="noopener">Hubungi</a>@endif
                </div>
            </aside>

            <form class="recruitment-form" action="{{ route('recruitment.applications.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <header>
                    <div><h2>Formulir Pendaftaran Calon Karyawan</h2><p>Pastikan nomor kontak WhatsApp aktif untuk menerima panggilan jadwal tes.</p></div>
                    <button type="button" data-application-close aria-label="Tutup formulir"><img src="{{ $recruitmentIcon(22) }}" alt=""></button>
                </header>

                @if ($errors->any())
                    <div class="recruitment-form-errors" role="alert"><strong>Periksa kembali data berikut:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="recruitment-form-grid">
                    <label>Nama Lengkap (Sesuai KTP) <b>*</b><input name="full_name" value="{{ old('full_name') }}" placeholder="Contoh: I Wayan Agus Pratama" required autocomplete="name"></label>
                    <label>Nomor Induk Kependudukan (NIK) <b>*</b><input name="nik" value="{{ old('nik') }}" placeholder="16 Digit NIK KTP Anda" inputmode="numeric" minlength="16" maxlength="16" required></label>
                    <label>Nomor WhatsApp Aktif <b>*</b><input name="whatsapp" value="{{ old('whatsapp') }}" placeholder="08xxxxxxxxxx (untuk konfirmasi)" required autocomplete="tel"></label>
                    <label>Alamat Email <b>*</b><input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autocomplete="email"></label>
                    <label>Formasi yang Dilamar <b>*</b><select name="job_vacancy_id" data-vacancy-select required><option value="">Pilih salah satu posisi...</option>@foreach ($vacancies as $vacancy)<option value="{{ $vacancy->id }}" @selected($oldVacancyId === (string) $vacancy->id)>{{ $vacancy->title }}</option>@endforeach</select></label>
                    <label>Domisili Saat Ini <b>*</b><input name="domicile" value="{{ old('domicile') }}" placeholder="Kabupaten/kota domisili Anda" required autocomplete="address-level2"></label>
                    <label class="recruitment-form-full">Cabang Penempatan yang Diminati <b>*</b><select name="branch_id" data-placement-select data-old-value="{{ old('branch_id') }}" required><option value="">Pilih posisi terlebih dahulu...</option></select></label>
                    <label class="recruitment-form-full">Ringkasan Pengalaman Terkait (Tahun &amp; Instansi)<textarea name="experience" rows="3" placeholder="Contoh: 2 tahun security perbankan di Denpasar (2022-2024), sertifikat Gada Pratama Polda Bali.">{{ old('experience') }}</textarea></label>
                </div>

                <label class="recruitment-upload">
                    <span>Unggah Berkas Gabungan (CV, KTP, Ijazah &amp; Sertifikat) <b>*</b></span>
                    <input class="sr-only" type="file" name="document" accept="application/pdf,.pdf" required data-application-file>
                    <span class="recruitment-dropzone"><img src="{{ $recruitmentIcon(25) }}" alt=""><strong>Tarik &amp; lepas PDF di sini atau <u>Pilih Dokumen</u></strong><small data-application-file-name>Format: PDF (Maksimum 10 MB)</small></span>
                </label>

                <label class="recruitment-consent"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>Saya menyatakan dengan sungguh-sungguh bahwa data dan berkas lamaran yang saya lampirkan adalah benar, asli, dan sah. Saya memahami proses recruitment PT. BDS tidak memungut biaya apa pun.</span></label>
                <button class="recruitment-submit" type="submit"><img src="{{ $recruitmentIcon(21) }}" alt=""> Kirim Berkas Lamaran Resmi</button>
            </form>
        </div>
    </dialog>

    <script type="application/json" data-vacancy-branches>{!! $vacancyBranches->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</body>
</html>
