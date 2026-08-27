<x-passenger.layout title="Selesai" variant="success">
    <section class="passenger-success-page">
        <div class="passenger-success-icon"><x-lucide-check aria-hidden="true" /></div>
        <h1>Terima Kasih!</h1>
        <p>Penilaian Anda telah berhasil dikirim.</p>
        <p class="passenger-success-note">Partisipasi Anda sangat berarti bagi kami untuk pelayanan yang lebih baik.</p>
        <a class="passenger-success-finish" href="{{ route('passenger.rating.vehicle', $vehicle->qr_token) }}">Detail Kendaraan</a>
    </section>
</x-passenger.layout>
