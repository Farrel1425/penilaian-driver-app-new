<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PT. Bali Dana Sejahtera: persewaan armada, tenaga alih daya, pengadaan dan percetakan, serta layanan pembersihan dan perawatan AC untuk institusi di Bali.">
    <link rel="icon" type="image/png" href="{{ asset('images/bds/bds-logo.png') }}">
    <title>PT. Bali Dana Sejahtera</title>
    {!! Illuminate\Support\Facades\Vite::fonts() !!}
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body class="bds-landing">
    @php
        $icon = fn ($number = '') => asset('images/bds/imgContainer'.$number.'.svg');
        $logo = asset('images/bds/bds-logo.png');
        $copyrightText = App\Models\SystemSetting::copyrightText();
        $partners = [
            ['Bank BPD Bali', 'Kantor Pusat & Seluruh Cabang', 'Mitra Utama Armada & SDM', 8],
            ['Bank Mandiri', 'Regional Bali & Nusa Tenggara', 'Rental Kendaraan Operasional', 9],
            ['Yayasan ICCO', 'International NGO', 'Mobilitas Eksekutif', 10],
            ['PT. GTA', 'General Trading Agency', 'Outsourcing & Logistik ATK', 11],
            ['Dana Pensiun BPD', 'Lembaga Pengelola', 'Kerjasama Korporat', 12],
            ['Metric Salon Bali', 'Sektor Swasta & Komersial', 'Percetakan & Promosi', 13],
            ['PT. Jamkrida Bali Mandara', 'Penjaminan Kredit Daerah Bali', 'BUMD Penjaminan Kredit', 'images/bds/icon-jamkrida.svg'],
            ['Lembaga Perkreditan Desa (LPD) se-Bali', 'Pengadaan cetak formulir bank, slip transaksi & perlengkapan administrasi kantor', 'Jaringan Se-Bali', 14],
        ];
        $standards = [
            ['SOP Terintegrasi', 'Menyelaraskan prosedur kerja internal BDS secara fleksibel dengan kode etik dan standar operasional klien.', 15],
            ['Evaluasi Rutin', 'Inspeksi berkala kinerja personel oleh supervisor BDS serta audit kelayakan mesin armada tiap bulan.', 16],
            ['Respon Cepat 24/7', 'Mitigasi darurat jika terjadi kendala teknis armada atau kebutuhan pergantian personel seketika se-Bali.', 17],
            ['Kepatuhan Hukum & K3', 'Ketaatan regulasi Disnaker RI, standar K3 keselamatan kerja, serta asuransi perlindungan BPJS teratur.', 18],
        ];
    @endphp
    <header class="bds-header">
        <div class="bds-header-inner">
            <a class="bds-brand" href="#beranda">
                <img src="{{ $logo }}" alt="Logo PT. Bali Dana Sejahtera" width="38" height="38">
                <span><strong>PT. Bali Dana Sejahtera</strong><small>Corporate Solutions • Est. 2007</small></span>
            </a>
            <nav class="bds-nav" aria-label="Navigasi utama">
                <a href="#beranda" aria-current="location">Beranda</a>
                <a href="#layanan">Layanan</a>
                <a href="#profil">Profil &amp; Legalitas</a>
                <a href="#mitra">Mitra Korporasi</a>
                <a href="#sop">SOP Kualitas</a>
                <a href="#hubungi">Hubungi</a>
            </nav>
            <a class="bds-button bds-login" href="{{ route('login') }}">Login <img src="{{ $icon(24) }}" alt="" width="9" height="9"></a>
        </div>
    </header>

    <main>
        <section id="beranda" class="bds-hero bds-shell">
            <div class="bds-hero-copy">
                <p class="bds-badge"><img src="{{ $icon() }}" alt="" width="15" height="14"> TERDAFTAR KEMENKUMHAM RI • SEJAK 2007</p>
                <h1>Solusi Anda<em>Prioritas</em> Kami</h1>
                <p class="bds-lead">PT. Bali Dana Sejahtera (PT. BDS) menghadirkan ekosistem terpadu persewaan armada kendaraan operasional, tenaga alih daya (outsourcing) tersertifikasi, serta pengadaan ATK &amp; percetakan resmi instansi perbankan di seluruh Bali.</p>
                <div class="bds-hero-actions">
                    <a class="bds-button" href="#hubungi">Hubungi Kami <img src="{{ $icon(1) }}" alt="" width="11" height="11"></a>
                    <a class="bds-button bds-button-outline" href="#profil">Pelajari Profil &amp; Legalitas</a>
                </div>
                <div class="bds-trust">
                    <div class="bds-avatar-stack" aria-hidden="true"><span>BPD</span><span>BM</span><span>LPD</span><span>+99</span></div>
                    <p><strong>100+ Entitas &amp; Mitra Korporasi</strong><small>Kepercayaan institusi perbankan, instansi, &amp; lembaga di Bali</small></p>
                </div>
            </div>
            <div class="bds-hero-visual">
                <div class="bds-photo">
                    <img class="bds-armada" src="{{ asset('images/bds/hero-driver-evaluation.png') }}" alt="Aplikasi Penilaian Driver PT. Bali Dana Sejahtera digunakan dari dalam kendaraan" width="417" height="329">
                    <a class="bds-photo-link" href="#layanan" aria-label="Lihat layanan armada"><img src="{{ $icon(2) }}" alt="" width="12" height="12"></a>
                    <div class="bds-photo-caption"><span>STANDAR PERBANKAN</span><strong>Pengelolaan 100+ Armada &amp; Tenaga Tersertifikasi</strong></div>
                </div>
                <div class="bds-readiness"><div><span>Kesiapan Armada</span><i aria-hidden="true"></i></div><p><strong>24/7</strong> <span>Siaga Se-Bali</span></p><small>Mobil Cadangan &amp; Reaksi Cepat</small></div>
            </div>
        </section>

        <section class="bds-stat-band" aria-label="Skala operasional">
            <div class="bds-stats bds-shell">
                <div><strong>100+</strong><b>Unit Armada Aktif</b><p>Toyota Avanza, Innova, VIP Fortuner &amp; Mobil Kas</p></div>
                <div><strong>100+</strong><b>Personel Tetap</b><p>Satpam Gada Pratama, Driver, CS &amp; Staf Kantor</p></div>
                <div><strong>232+</strong><b>Personel Outsourcing</b><p>Satpam Gada Pratama, Driver, CS &amp; Staf Kantor</p></div>
                <div><strong>17+</strong><b>Tahun Pengalaman</b><p>Rekam jejak kredibel melayani korporat sejak 2007</p></div>
            </div>
        </section>

        <section id="layanan" class="bds-services-section bds-shell">
            <div class="bds-section-heading">
                <div><p class="bds-eyebrow">PILAR SOLUSI KORPORAT</p><h2>Solusi Empat Pilar Bisnis<br><em>Menjawab Segala Kebutuhan</em> Operasional<br>Anda</h2></div>
            </div>
            <div class="bds-services">
                <article class="bds-service-card">
                    <div class="bds-service-label"><span>Layanan 01</span><small><img src="{{ $icon(3) }}" alt="">100+ Unit</small></div>
                    <h3>Persewaan Armada Kendaraan</h3>
                    <p>Armada representatif terawat dengan garansi penggantian unit 24 jam dan asuransi all-risk komprehensif.</p>
                    <dl class="bds-fleet">
                        @foreach (['Toyota Avanza (Dinas)' => '72 Unit', 'Innova Reborn & Zenix' => '8 Unit', 'Toyota Fortuner & Voxy' => '10 Unit', 'Mobil Kas Keliling Bank' => '7 Unit', 'Grand Max & Sepeda Motor' => '5 Unit'] as $name => $total)
                            <div><dt>{{ $name }}</dt><dd>{{ $total }}</dd></div>
                        @endforeach
                    </dl>
                    <a class="bds-card-footer" href="#hubungi">Asuransi All-Risk &amp; Servis Rutin <span><img src="{{ $icon(4) }}" alt=""></span></a>
                </article>
                <article class="bds-service-card">
                    <div class="bds-service-label"><span>Layanan 02</span><small><img src="{{ $icon(5) }}" alt="">232+ Personel</small></div>
                    <h3>Tenaga Alih Daya Terpadu</h3>
                    <p>Pengelolaan SDM berintegritas tinggi dengan nilai: <strong>Loyal, Jujur, Disiplin, Terbuka, &amp; Bertanggung Jawab.</strong></p>
                    <div class="bds-personnel">
                        <div><strong>Satuan Pengamanan</strong><p>Sertifikat Gada Pratama &amp; KTA Aktif</p></div>
                        <div><strong>Pengemudi / Driver</strong><p>Hafal rute Bali &amp; safety driving</p></div>
                        <div><strong>Cleaning Service</strong><p>Standar sanitasi perkantoran</p></div>
                        <div><strong>Staf Administrasi</strong><p>Petugas DRC, Arsip, &amp; Operator</p></div>
                    </div>
                    <a class="bds-card-footer" href="#hubungi">Kepatuhan UMK &amp; BPJS Penuh <span><img src="{{ $icon(4) }}" alt=""></span></a>
                </article>
                <article class="bds-service-card">
                    <div class="bds-service-label"><span>Layanan 03</span><small><img src="{{ $icon(6) }}" alt="">Presisi Tinggi</small></div>
                    <h3>Perdagangan dan Percetakan</h3>
                    <p>Pengadaan dokumen sekuriti perbankan, continuous form, dan suplai kebutuhan ATK rutin kantor cabang se-Bali.</p>
                    <ul class="bds-supplies">
                        @foreach (['Buku Cek, Formulir & Giro', 'Continuous Form Aneka Gramatur', 'Slip Transaksi & Amplop Kop', 'Distribusi Cepat Wilayah Bali'] as $item)
                            <li><img src="{{ $icon(7) }}" alt="">{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a class="bds-card-footer" href="#hubungi">Standar Kerapihan Perbankan <span><img src="{{ $icon(4) }}" alt=""></span></a>
                </article>
                <article class="bds-service-card">
                    <div class="bds-service-label"><span>Layanan 04</span><small><img src="{{ asset('images/bds/icon-cleaning-ac.svg') }}" alt="">Kebersihan &amp; AC</small></div>
                    <h3>Kelola Pembersihan &amp; Perawatan AC</h3>
                    <p>Layanan terintegrasi pembersihan gedung kantor, sanitasi berkala, serta instalasi dan pemeliharaan sistem AC pendingin ruangan berstandar prima.</p>
                    <ul class="bds-supplies">
                        @foreach (['Servis & Cuci AC Berkala (Split, Cassette, Central)', 'Pengadaan & Instalasi Pendingin Ruangan', 'Deep Cleaning & Sanitasi Gedung Kantor', 'Perawatan Saluran Udara & Filter Berkala'] as $item)
                            <li><img src="{{ $icon(7) }}" alt="">{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a class="bds-card-footer" href="#hubungi">Teknisi AC Bersertifikat &amp; Higienis <span><img src="{{ $icon(4) }}" alt=""></span></a>
                </article>
            </div>
        </section>

        <section id="profil" class="bds-director-section">
            <div class="bds-shell"><div class="bds-director">
                <span class="bds-dark-badge">TATA KELOLA &amp; INTEGRITAS</span>
                <h2>Prakata Direksi &amp; Komitmen<br><em>Tata Kelola Perusahaan</em></h2>
                <blockquote>"Kami mengemban dedikasi untuk menjaga kepuasan mitra bisnis melalui layanan berkualitas tinggi, respon tanggap darurat yang cepat, serta tata kelola yang transparan. Setiap personel kami dibekali pembinaan etika kerja, kedisiplinan dan rasa tanggung jawab penuh demi kelancaran operasional mitra perbankan dan korporat kami di Bali."</blockquote>
                <div class="bds-director-profile"><img src="{{ asset('images/bds/direktur.png') }}" alt="Ida Bagus Gede Ary Wijaya Guntur" width="56" height="56"><div><strong>Ida Bagus Gede Ary Wijaya Guntur, S.E., M.M.</strong><span>Direktur PT. Bali Dana Sejahtera</span><small>Amanat Tata Kelola &amp; Transformasi Bisnis Berkelanjutan</small></div></div>
            </div></div>
        </section>

        <section id="mitra" class="bds-partners bds-shell">
            <div class="bds-centered-heading"><p class="bds-eyebrow">JEJARING KEPERCAYAAN</p><h2>Mitra Klien &amp; Rekanan Institusional</h2><p>Menjadi bagian tak terpisahkan dari operasional perbankan terkemuka, lembaga pembangunan, dan korporasi swasta di Bali.</p></div>
            <div class="bds-partner-grid">
                @foreach ($partners as [$name, $description, $service, $image])
                    <article class="bds-partner"><span class="bds-partner-icon"><img src="{{ is_int($image) ? $icon($image) : asset($image) }}" alt=""></span><h3>{{ $name }}</h3><p>{{ $description }}</p><small>{{ $service }}</small></article>
                @endforeach
            </div>
        </section>

        <section id="sop" class="bds-standards">
            <div class="bds-shell">
                <div class="bds-centered-heading"><p class="bds-eyebrow">OPERATIONAL EXCELLENCE</p><h2>Standar Mutu (SOP) &amp; Jaminan Bebas Risiko</h2><p>Mekanisme kendali mutu berlapis untuk memastikan kelancaran operasional bisnis mitra tanpa hambatan.</p></div>
                <div class="bds-standard-grid">
                    @foreach ($standards as [$title, $copy, $image])
                        <article><span class="bds-standard-icon"><img src="{{ $icon($image) }}" alt=""></span><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="hubungi" class="bds-contact-section">
            <div class="bds-contact">
                <div class="bds-contact-copy">
                    <div><span class="bds-dark-badge"><img src="{{ $icon(19) }}" alt="">Respon Cepat &lt; 24 Jam Kerja</span><h2>Mulai Kemitraan Bersama PT. BDS</h2><p>Diskusikan kebutuhan sewa armada, tenaga alih daya bersertifikasi, pengadaan dan percetakan, atau layanan pembersihan dan perawatan AC.</p></div>
                    <address>
                        <div><span><img src="{{ $icon(20) }}" alt=""></span><p><strong>Kantor Operasional &amp; Sekretariat:</strong>Denpasar, Bali - Indonesia (Melayani seluruh kabupaten/kota se-Bali)</p></div>
                        <div><span><img src="{{ $icon(21) }}" alt=""></span><p><strong>Surat Elektronik (Email):</strong><a href="mailto:sekretariat@balidanasejahtera.co.id">sekretariat@balidanasejahtera.co.id</a> /<br><a href="mailto:corporate@bdsbali.com">corporate@bdsbali.com</a></p></div>
                        <div><span><img src="{{ $icon(22) }}" alt=""></span><p><strong>Hotline Layanan B2B &amp; WhatsApp:</strong>(0361) BDS-CORP / Layanan Konsultasi Cepat</p></div>
                    </address>
                    <small>Sistem kerahasiaan data calon mitra dijamin sesuai regulasi perlindungan data yang berlaku di Republik Indonesia.</small>
                </div>
                <form class="bds-contact-form" method="POST" action="{{ route('partnership-inquiries.store') }}" data-proposal-form>
                    @csrf
                    <h3>Formulir Permintaan Penawaran Resmi</h3>
                    <p>Lengkapi formulir singkat di bawah ini, tim representatif kami akan segera menghubungi Anda.</p>
                    @if (session('inquiry_status'))
                        <div class="bds-form-feedback is-success" data-proposal-feedback role="status">{{ session('inquiry_status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="bds-form-feedback is-error" data-proposal-feedback role="alert">
                            <strong>Formulir belum dapat dikirim.</strong>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="bds-form-grid">
                        <label>Nama Perusahaan / Instansi *<input name="company_name" value="{{ old('company_name') }}" placeholder="PT / CV / Lembaga" required autocomplete="organization"></label>
                        <label>Nama PIC &amp; Jabatan *<input name="contact_name" value="{{ old('contact_name') }}" placeholder="Nama PIC Pengadaan" required autocomplete="name"></label>
                        <label>Nomor WhatsApp PIC *<input name="whatsapp" value="{{ old('whatsapp') }}" type="tel" placeholder="0812-xxxx-xxxx" required autocomplete="tel"></label>
                        <label>Email Resmi *<input name="email" value="{{ old('email') }}" type="email" placeholder="pic@perusahaan.co.id" required autocomplete="email"></label>
                        <label class="bds-field-wide">Kebutuhan Layanan *<select name="service" required><option @selected(old('service') === 'Persewaan Armada Kendaraan (Avanza, Innova, VIP, Kas Mobil)')>Persewaan Armada Kendaraan (Avanza, Innova, VIP, Kas Mobil)</option><option @selected(old('service') === 'Tenaga Alih Daya Terpadu')>Tenaga Alih Daya Terpadu</option><option @selected(old('service') === 'Perdagangan dan Percetakan')>Perdagangan dan Percetakan</option><option @selected(old('service') === 'Kelola Pembersihan & Perawatan AC')>Kelola Pembersihan &amp; Perawatan AC</option></select></label>
                        <label>Estimasi Kebutuhan Unit/Personel<input name="estimated_need" value="{{ old('estimated_need') }}" placeholder="Contoh: 5 Unit Armada / 10 Satpam"></label>
                        <label>Durasi Kontrak Kerjasama<select name="contract_duration"><option @selected(old('contract_duration') === 'Kontrak Tahunan (1 - 3 Tahun)')>Kontrak Tahunan (1 - 3 Tahun)</option><option @selected(old('contract_duration') === 'Kurang dari 1 Tahun')>Kurang dari 1 Tahun</option><option @selected(old('contract_duration') === 'Lebih dari 3 Tahun')>Lebih dari 3 Tahun</option></select></label>
                        <label class="bds-field-wide">Keterangan Tambahan<textarea name="notes" rows="3" placeholder="Sebutkan detail rute dinas, kualifikasi khusus, atau jadwal pengiriman proposal...">{{ old('notes') }}</textarea></label>
                    </div>
                    <button class="bds-button" type="submit">Kirim Permintaan Kerjasama &amp; Minta Proposal <img src="{{ $icon(23) }}" alt=""></button>
                </form>
            </div>
        </section>
    </main>

    <footer class="bds-footer">
        <div class="bds-shell">
            <div class="bds-footer-grid">
                <div class="bds-footer-brand"><a class="bds-brand" href="#beranda"><img src="{{ $logo }}" alt="Logo PT. Bali Dana Sejahtera" width="38" height="38"><strong>PT. Bali Dana Sejahtera</strong></a><p>Perusahaan penyedia persewaan kendaraan operasional, tenaga alih daya tersertifikasi, pengadaan dan percetakan, serta layanan pembersihan dan perawatan AC di Bali sejak 2007.</p><small>SK Kemenkumham RI Terdaftar Resmi</small></div>
                <div><h3>LAYANAN UTAMA</h3><a href="#layanan">Persewaan Armada Mobil Dinas</a><a href="#layanan">Mobil Kas Keliling Bank</a><a href="#layanan">Tenaga Driver &amp; Cleaning Service</a><a href="#layanan">Continuous Form &amp; ATK Bank</a><a href="#layanan">Pembersihan &amp; Perawatan AC</a></div>
                <div><h3>INFORMASI KORPORAT</h3><a href="#beranda">Beranda</a><a href="#profil">Profil &amp; Kepemilikan</a><a href="#mitra">Mitra Korporasi</a><a href="#sop">SOP &amp; Jaminan Kualitas</a><a href="#hubungi">Hubungi Sekretariat</a><a class="bds-footer-login" href="{{ route('login') }}">Login</a></div>
                <div><h3>KEPATUHAN HUKUM</h3><p>Disnaker Prov. Bali</p><p>BPJS Ketenagakerjaan</p><p>BPJS Kesehatan</p><p>Standar K3 Operasional</p></div>
            </div>
            <div class="bds-footer-bottom">
                <div class="bds-footer-credit">
                    @if ($copyrightText)<span>{{ $copyrightText }}</span>@endif
                    @if ($copyrightText)<span class="bds-footer-divider" aria-hidden="true">|</span>@endif
                    <span class="bds-footer-attribution">
                        <span>Design by</span>
                        <a class="bds-maiharta-mark" href="https://www.maiharta.com/home" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi situs Maiharta">
                            <img src="{{ asset('images/maiharta-logo.png') }}" alt="Maiharta" width="88" height="21">
                        </a>
                    </span>
                </div>
                <span class="bds-footer-location"><i></i> Denpasar, Bali</span>
            </div>
        </div>
    </footer>
    <nav class="bds-mobile-nav" aria-label="Navigasi mobile">
        <a href="#beranda" data-sections="beranda" aria-current="location"><img src="{{ asset('images/bds/mobile-nav-home.svg') }}" alt=""><span>Beranda</span></a>
        <a href="#layanan" data-sections="layanan"><img src="{{ asset('images/bds/mobile-nav-services.svg') }}" alt=""><span>Layanan</span></a>
        <a href="#mitra" data-sections="profil mitra sop"><img src="{{ asset('images/bds/mobile-nav-clients.svg') }}" alt=""><span>Klien</span></a>
        <a href="#hubungi" data-sections="hubungi"><img src="{{ asset('images/bds/mobile-nav-contact.svg') }}" alt=""><span>Hubungi</span></a>
    </nav>
</body>
</html>
