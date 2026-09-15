<div class="vehicle-qr-modal" data-vehicle-qr-modal hidden>
    <button class="vehicle-qr-modal-backdrop" type="button" data-vehicle-qr-close aria-label="Tutup popup QR"></button>
    <section class="vehicle-qr-dialog" role="dialog" aria-modal="true" aria-labelledby="vehicle-qr-modal-title">
        <button class="vehicle-qr-close" type="button" data-vehicle-qr-close aria-label="Tutup popup QR">
            <x-lucide-x aria-hidden="true" />
        </button>
        <span class="vehicle-qr-dialog-label">QR Kendaraan</span>
        <h2 id="vehicle-qr-modal-title" data-vehicle-qr-title>QR Kendaraan</h2>
        <p data-vehicle-qr-description></p>
        <div class="vehicle-qr-large-frame">
            <img data-vehicle-qr-image src="" alt="">
        </div>
        <a class="primary-button vehicle-qr-download" data-vehicle-qr-download data-no-loading download href="#">
            <x-lucide-download aria-hidden="true" />
            <span>Download QR</span>
        </a>
    </section>
</div>
