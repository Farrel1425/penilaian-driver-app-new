# Sistem Penilaian Driver

Aplikasi web untuk mengelola penilaian driver dan kendaraan berbasis QR Code. Penumpang tidak perlu login: cukup memindai QR kendaraan, memilih driver aktif pada unit kerja yang sama, lalu mengirim penilaian. Administrator mengelola master data, memantau riwayat, melihat laporan, dan mengatur sistem.

## Teknologi

- Laravel 13 dan PHP 8.3+
- MySQL 8+
- Blade, Eloquent ORM, Form Request, dan middleware Laravel
- Vite, JavaScript, dan CSS
- Endroid QR Code
- Blade Lucide Icons dan Cropper.js

## Fitur Utama

### Admin

- Autentikasi admin, ingat saya, reset kata sandi, dan profil admin.
- Dashboard dengan filter periode/unit kerja, statistik, tren, distribusi, aktivitas, dan peringkat.
- Master Unit Kerja, Driver, Kendaraan, Pertanyaan, serta akun Admin.
- Unggah/crop foto driver, SIM, kendaraan, dan profil admin.
- QR kendaraan: preview, unduh, cetak, dan regenerasi token.
- Riwayat dan rekap penilaian, monitoring, laporan Driver/Kendaraan/Unit Kerja, serta ekspor data.
- Log aktivitas administrator.
- Popup konfirmasi untuk aktifkan/nonaktifkan dan hapus data.

### Penumpang

- Akses publik melalui `/rating/{vehicleToken}`.
- Informasi kendaraan, pemilihan driver, detail driver, penilaian, dan halaman sukses.
- Pertanyaan aktif ditampilkan sesuai urutan dan target Driver/Kendaraan/Feedback.
- Tipe jawaban: rating, ya/tidak, pilihan ganda, checkbox, jawaban singkat, dan paragraf.

## Aturan Bisnis Penting

1. Driver dan kendaraan memiliki `branch_id` atau Unit Kerja.
2. Driver tidak memiliki penugasan kendaraan permanen.
3. Setelah QR kendaraan dipindai, hanya driver aktif dari unit kerja kendaraan yang boleh dipilih.
4. Kendaraan tidak aktif tidak dapat digunakan untuk penilaian.
5. Driver dan pertanyaan tidak aktif tidak tampil pada alur penumpang.
6. Pertanyaan ditampilkan menurut `sort_order`.
7. Nilai rating hanya `1` sampai `5`; ya/tidak menggunakan `1` dan `0`.
8. Nilai ya/tidak tidak dimasukkan ke perhitungan rata-rata rating 1-5.
9. Menonaktifkan unit kerja akan menonaktifkan driver dan kendaraan terkait. Mengaktifkan kembali unit kerja tidak otomatis mengaktifkan keduanya.

## Persiapan Lokal

### 1. Instal Dependensi

```powershell
composer install
npm install
```

### 2. Siapkan Environment

Salin contoh konfigurasi bila file `.env` belum ada.

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Atur koneksi MySQL pada `.env`:

```dotenv
APP_NAME="Sistem Penilaian Driver"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=penilaian_driver
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `penilaian_driver` melalui MySQL/Laragon, lalu jalankan migrasi:

```powershell
php artisan migrate
php artisan storage:link
```

### 3. Data Development Opsional

Untuk database development yang masih kosong, buat akun dan data contoh:

```powershell
php artisan db:seed
php artisan db:seed --class=CompleteDemoDataSeeder
```

Login development awal yang dibuat oleh `DatabaseSeeder`:

```text
Email    : admin@example.com
Password : password
```

Segera ubah kata sandi akun tersebut setelah login.

> **Peringatan data:** `DatabaseSeeder`, `DevelopmentSeeder`, dan `DemoDataSeeder` menjalankan `ClientQuestionSeeder`. Seeder ini menghapus jawaban dan pertanyaan sebelum membuat pertanyaan contoh baru. Jalankan seeder hanya pada database development/baru. Jangan gunakan `migrate:fresh`, `db:wipe`, atau seeder demo pada database kerja tanpa backup dan persetujuan yang jelas.

## Menjalankan Aplikasi

Jalankan dua terminal pada mode development:

```powershell
php artisan serve
```

```powershell
npm run dev
```

Buka [http://localhost:8000](http://localhost:8000). Aplikasi akan mengarahkan ke halaman login admin.

Untuk build aset production:

```powershell
npm run build
```

## Konfigurasi Email Reset Password

Fitur lupa kata sandi membutuhkan konfigurasi mail yang valid. Contoh SMTP pada `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=nama_pengguna
MAIL_PASSWORD=kata_sandi
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Saat `MAIL_MAILER=log`, tautan reset dicatat di file log Laravel dan tidak dikirim ke email asli.

## Pengujian dan Kualitas Kode

```powershell
php artisan test
vendor\bin\pint --dirty
php artisan view:clear
php artisan view:cache
npm run build
```

## Struktur Ringkas

```text
app/
  Http/Controllers/     Controller admin, autentikasi, dan passenger flow
  Models/               Model dan relasi Eloquent
  Services/             Layanan QR Code dan layanan aplikasi
database/
  migrations/           Struktur database
  seeders/              Data development dan dummy
resources/
  views/                Blade admin dan halaman penumpang
  css/app.css           Tema serta layout aplikasi
  js/app.js             Interaksi frontend
routes/web.php          Route publik dan admin
tests/Feature/          Pengujian fitur aplikasi
```

## Dokumen Referensi

- `AGENTS.md`: konteks proyek dan aturan pengembangan.
- `PROJECT_SPEC_PENILAIAN_DRIVER_V3.md`: sumber utama kebutuhan fungsional dan aturan bisnis.
- `aplikasi penilaian driver.pdf`: referensi tata letak dan tampilan visual.

## Catatan Keamanan

- Jangan menyimpan kredensial atau nilai `.env` di repository.
- Pastikan `APP_DEBUG=false` pada production.
- Gunakan password database yang kuat pada production.
- Backup database sebelum menjalankan migrasi atau seeder pada server yang berisi data penting.
