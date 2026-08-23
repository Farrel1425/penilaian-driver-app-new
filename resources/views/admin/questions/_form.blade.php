@csrf
@php
    $backUrl = isset($returnTo) && $returnTo === 'detail' ? route('admin.questions.show', $question) : route('admin.questions.index');
@endphp
@if (isset($returnTo))
    <input type="hidden" name="return_to" value="{{ $returnTo }}">
@endif
@php
    $driverBaseWeight = $weightSummary[App\Models\Question::TARGET_DRIVER] ?? 0;
    $vehicleBaseWeight = $weightSummary[App\Models\Question::TARGET_VEHICLE] ?? 0;
    $optionRows = collect(old('options', $question->exists ? $question->options->map(fn ($option) => ['option_text' => $option->option_text, 'sort_order' => $option->sort_order])->all() : [
        ['option_text' => '', 'sort_order' => 1],
        ['option_text' => '', 'sort_order' => 2],
    ]))->values();
@endphp

<div class="question-form-layout">
    <div>
        <div class="form-section-title">Informasi Pertanyaan</div>
        <div class="form-grid">
            <x-admin.textarea label="Pertanyaan" name="question" :value="$question->question" required data-question-input />
            <x-admin.select label="Target" name="target_type" required data-weight-target>
                <option value="{{ App\Models\Question::TARGET_DRIVER }}" @selected(old('target_type', $question->target_type ?? App\Models\Question::TARGET_DRIVER) === App\Models\Question::TARGET_DRIVER)>Driver</option>
                <option value="{{ App\Models\Question::TARGET_VEHICLE }}" @selected(old('target_type', $question->target_type) === App\Models\Question::TARGET_VEHICLE)>Kendaraan</option>
            </x-admin.select>
            <x-admin.field label="Indikator" name="indicator" :value="$question->indicator" required data-indicator-input />
            <x-admin.select label="Tipe Jawaban" name="answer_type" required data-answer-type>
                @foreach ([
                    App\Models\Question::TYPE_RATING => 'Rating 1-5',
                    App\Models\Question::TYPE_YES_NO => 'Ya/Tidak',
                    App\Models\Question::TYPE_MULTIPLE_CHOICE => 'Pilihan Ganda',
                    App\Models\Question::TYPE_CHECKBOX => 'Checkbox',
                    App\Models\Question::TYPE_SHORT_TEXT => 'Jawaban Singkat',
                    App\Models\Question::TYPE_PARAGRAPH => 'Paragraf',
                ] as $value => $label)
                    <option value="{{ $value }}" @selected(old('answer_type', $question->answer_type ?? App\Models\Question::TYPE_RATING) === $value)>{{ $label }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.select label="Wajib" name="is_required" required>
                <option value="1" @selected((string) old('is_required', (int) ($question->is_required ?? true)) === '1')>Wajib</option>
                <option value="0" @selected((string) old('is_required', (int) ($question->is_required ?? true)) === '0')>Tidak Wajib</option>
            </x-admin.select>
            <x-admin.field label="Bobot (%)" name="weight" type="number" :value="$question->weight" min="1" max="100" required data-question-weight />
            <x-admin.select label="Status" name="status" required>
                <option value="{{ App\Models\Question::STATUS_ACTIVE }}" @selected(old('status', $question->status ?? App\Models\Question::STATUS_ACTIVE) === App\Models\Question::STATUS_ACTIVE)>Aktif</option>
                <option value="{{ App\Models\Question::STATUS_INACTIVE }}" @selected(old('status', $question->status) === App\Models\Question::STATUS_INACTIVE)>Nonaktif</option>
            </x-admin.select>
        </div>

        <div class="form-section-title">Pengaturan Tampilan</div>
        <div class="form-grid">
            <x-admin.textarea label="Deskripsi / Petunjuk" name="instruction" :value="$question->instruction" data-question-instruction />
            <x-admin.field label="Placeholder" name="placeholder" :value="$question->placeholder" data-question-placeholder />
            <x-admin.field label="Label Skala 1" name="rating_min_label" :value="old('rating_min_label', $question->rating_min_label ?: 'Sangat Buruk')" data-question-rating-min />
            <x-admin.field label="Label Skala 5" name="rating_max_label" :value="old('rating_max_label', $question->rating_max_label ?: 'Sangat Baik')" data-question-rating-max />
            <x-admin.image-cropper label="Ikon / Gambar Pertanyaan" name="icon" :value="$question->icon_path" data-question-icon-cropper />
        </div>

        <aside class="question-weight-summary" data-weight-summary data-driver-base="{{ $driverBaseWeight }}" data-vehicle-base="{{ $vehicleBaseWeight }}">
            <div>
                <span>Bobot {{ old('target_type', $question->target_type ?? App\Models\Question::TARGET_DRIVER) === App\Models\Question::TARGET_DRIVER ? 'Driver' : 'Kendaraan' }}</span>
                <strong data-weight-used>0%</strong>
            </div>
            <div>
                <span>Sisa bobot yang dapat diinput</span>
                <strong data-weight-remaining>100%</strong>
            </div>
            <p>Total bobot setiap target harus tepat 100% sebelum pertanyaannya dapat diaktifkan.</p>
        </aside>

        <div class="option-builder" data-option-builder>
            <div class="form-section-title">Opsi Jawaban</div>
            <p class="form-help">Digunakan hanya untuk Pilihan Ganda dan Checkbox.</p>
            @error('options')<small class="form-error">{{ $message }}</small>@enderror
            <div class="option-list" data-option-list>
                @foreach ($optionRows as $index => $option)
                    <div class="option-row" data-option-row>
                        <input type="text" name="options[{{ $index }}][option_text]" value="{{ $option['option_text'] ?? '' }}" placeholder="Opsi jawaban" data-option-text>
                        <input type="number" name="options[{{ $index }}][sort_order]" value="{{ $option['sort_order'] ?? $index + 1 }}" min="0" aria-label="Urutan opsi">
                        <button class="icon-inline-button" type="button" data-remove-option aria-label="Hapus opsi">×</button>
                    </div>
                @endforeach
            </div>
            <button class="secondary-button" type="button" data-add-option>Tambah Opsi</button>
        </div>

        <div class="form-actions">
            <a class="secondary-button" href="{{ $backUrl }}">Batal</a>
            <button class="primary-button" type="submit">Simpan</button>
        </div>
    </div>

    <x-admin.panel title="Preview & Aksi" class="question-preview-panel">
        <x-admin.question-preview :question="$question" :options="$question->exists ? $question->options : collect()" />
    </x-admin.panel>
</div>
