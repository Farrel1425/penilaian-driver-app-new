@props([
    'name',
    'label',
    'value' => null,
    'folder' => 'images',
    'aspectRatio' => 1,
    'cropLabel' => '1:1',
    'variant' => null,
])

@php
    $inputId = 'image-cropper-'.str($name)->replace('_', '-').'-'.str()->random(8);
    $previewUrl = $value
        ? (Str::startsWith($value, ['http://', 'https://', '/']) ? $value : asset('storage/'.$value))
        : null;
@endphp

<div {{ $attributes }} @class([
    'image-cropper-field',
    'image-cropper-field--document' => (float) $aspectRatio !== 1.0,
    'image-cropper-field--vehicle' => $variant === 'vehicle',
]) data-image-cropper data-image-cropper-ratio="{{ $aspectRatio }}">
    <div class="image-cropper-label-row">
        <label for="{{ $inputId }}">{{ $label }}</label>
        <span>Opsional</span>
    </div>

    <div class="image-cropper-summary" data-image-open-picker role="button" tabindex="0" aria-label="Pilih atau ganti {{ strtolower($label) }}">
        <div class="image-cropper-preview" data-image-preview>
            @if ($previewUrl)
                <img src="{{ $previewUrl }}" alt="Preview {{ strtolower($label) }}">
            @else
                <x-lucide-image aria-hidden="true" />
            @endif
        </div>
        <div class="image-cropper-summary-copy">
            <strong data-image-file-name>{{ $previewUrl ? 'Foto saat ini' : 'Belum ada foto' }}</strong>
            <span>JPG, PNG, atau WEBP. Maksimal 4 MB.</span>
        </div>
        <x-lucide-chevron-right class="image-cropper-summary-arrow" aria-hidden="true" />
    </div>

    <input id="{{ $inputId }}" name="{{ $name }}" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-image-input>
    <input type="file" accept="image/*" capture="environment" class="sr-only" data-camera-input>
    @error($name)<span class="form-error">{{ $message }}</span>@enderror

    <div class="image-cropper-modal" data-image-modal hidden>
        <div class="image-cropper-modal-backdrop" data-image-close></div>
        <section class="image-cropper-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $inputId }}-title">
            <div class="image-cropper-dialog-header">
                <div>
                    <h2 id="{{ $inputId }}-title">Atur Foto</h2>
                    <p>Geser gambar untuk menentukan area yang ditampilkan.</p>
                </div>
                <button type="button" class="image-cropper-close" data-image-close aria-label="Tutup editor foto"><x-lucide-x /></button>
            </div>

            <div class="image-source-picker" data-image-source-picker>
                <div class="image-drop-zone" data-image-drop-zone tabindex="0" role="button" aria-label="Pilih foto dari perangkat">
                    <x-lucide-upload-cloud aria-hidden="true" />
                    <strong>Tarik foto ke sini</strong>
                    <span>atau klik untuk memilih file</span>
                    <small>JPG, PNG, atau WEBP. Maksimal 4 MB.</small>
                </div>
                <button type="button" class="image-camera-link" data-image-camera><x-lucide-camera aria-hidden="true" /> Ambil Foto</button>
            </div>

            <div class="image-cropper-stage" data-image-stage>
                <img data-cropper-image alt="Atur area foto">
                <div class="image-camera-stage" data-camera-stage hidden>
                    <video data-camera-video autoplay playsinline></video>
                    <p data-camera-message>Kamera sedang disiapkan...</p>
                </div>
            </div>

            <div class="image-cropper-controls" data-cropper-controls>
                <span class="image-cropper-ratio-label">Crop {{ $cropLabel }}</span>
                <label for="{{ $inputId }}-zoom">Zoom</label>
                <input id="{{ $inputId }}-zoom" type="range" min="-0.3" max="0.7" value="0" step="0.01" data-cropper-zoom>
                <button type="button" class="image-cropper-text-button" data-cropper-reset>Atur Ulang</button>
            </div>

            <div class="image-cropper-camera-actions" data-camera-actions hidden>
                <button type="button" class="secondary-button" data-camera-retry>Ganti Kamera</button>
                <button type="button" class="primary-button" data-camera-capture><x-lucide-aperture aria-hidden="true" /> Ambil Gambar</button>
            </div>

            <div class="image-cropper-dialog-actions" data-cropper-actions>
                <button type="button" class="secondary-button" data-image-close>Batal</button>
                <button type="button" class="primary-button" data-cropper-apply>Simpan Crop</button>
            </div>
        </section>
    </div>
</div>
