<x-passenger.layout title="Selesai" variant="success">
    <section class="passenger-success-page">
        <div class="passenger-success-icon"><x-lucide-check aria-hidden="true" /></div>
        <h1>Terima Kasih!</h1>
        <p>Penilaian Anda telah berhasil dikirim.</p>
        <p class="passenger-success-note">Kontribusi Anda berarti bagi peningkatan layanan Aplikasi Penilaian Driver.</p>
        <div class="passenger-success-reference"><x-lucide-file-text aria-hidden="true" /><div><span>ID Referensi</span><strong>VC-{{ str_pad((string) $rating->id, 6, '0', STR_PAD_LEFT) }}</strong></div></div>
        <a class="passenger-success-finish" href="{{ route('passenger.rating.vehicle', $vehicle->qr_token) }}"><x-lucide-house aria-hidden="true" /> Kembali ke Beranda</a>
    </section>
</x-passenger.layout>
