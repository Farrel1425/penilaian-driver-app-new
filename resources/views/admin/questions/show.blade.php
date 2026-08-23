<x-layouts.admin title="Detail Pertanyaan">
    <div class="resource-page-navigation"><a class="primary-button" href="{{ route('admin.questions.index') }}"><x-lucide-arrow-left aria-hidden="true" /><span>Kembali</span></a></div>
    <div class="detail-layout branch-detail-layout">
        <x-admin.panel title="Informasi Pertanyaan">
            <div class="detail-grid">
                <x-admin.detail-row label="Pertanyaan" :value="$question->question" />
                <x-admin.detail-row label="Deskripsi / Petunjuk" :value="$question->instruction ?: '-'" />
                <x-admin.detail-row label="Placeholder" :value="$question->placeholder ?: '-'" />
                <x-admin.detail-row label="Target" :value="$question->target_type === 'driver' ? 'Driver' : 'Kendaraan'" />
                <x-admin.detail-row label="Indikator" :value="$question->indicator" />
                <x-admin.detail-row label="Tipe Jawaban" :value="str($question->answer_type)->replace('_', ' ')->title()" />
                <x-admin.detail-row label="Wajib" :value="$question->is_required ? 'Wajib' : 'Opsional'" />
                <x-admin.detail-row label="Bobot" :value="$question->weight . '%'" />
                <x-admin.detail-row label="Label Skala" :value="($question->rating_min_label ?: 'Sangat Buruk') . ' - ' . ($question->rating_max_label ?: 'Sangat Baik')" />
                <x-admin.detail-row label="Urutan" :value="$question->sort_order" />
                <x-admin.detail-row label="Status" :value="$question->status === 'active' ? 'Aktif' : 'Nonaktif'" />
            </div>
            @if ($question->icon_path)
                <div class="question-detail-icon"><span>Ikon / Gambar Pertanyaan</span><img src="{{ Str::startsWith($question->icon_path, ['http://', 'https://', '/']) ? $question->icon_path : asset('storage/'.$question->icon_path) }}" alt="Ikon {{ $question->question }}"></div>
            @endif
            @if($question->options->isNotEmpty())
                <div class="option-readonly-list">
                    <div class="form-section-title">Opsi Jawaban</div>
                    @foreach($question->options as $option)<div><span>{{ $option->sort_order }}</span><strong>{{ $option->option_text }}</strong></div>@endforeach
                </div>
            @endif
        </x-admin.panel>

        <x-admin.panel title="Preview & Aksi">
            <x-admin.question-preview :question="$question" :options="$question->options" />
            <div class="question-answer-summary"><strong>{{ $question->rating_answers_count }}</strong><span>Jawaban Tersimpan</span></div>
            <div class="form-actions stack-actions"><a class="primary-button" href="{{ route('admin.questions.edit', ['question' => $question, 'return_to' => 'detail']) }}">Edit</a><form method="POST" action="{{ route('admin.questions.toggle-status', $question) }}" @if ($question->status === 'active') data-confirm data-no-loading data-confirm-title="Nonaktifkan pertanyaan?" data-confirm-description="Pertanyaan ini tidak akan muncul pada penilaian penumpang sampai diaktifkan kembali." data-confirm-label="Nonaktifkan" data-confirm-tone="primary" data-confirm-icon="power" @endif>@csrf @method('PATCH')<button class="secondary-button" type="submit">{{ $question->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button></form><form method="POST" action="{{ route('admin.questions.destroy', $question) }}" data-delete-confirm data-no-loading data-delete-name="Pertanyaan {{ $question->question }}" data-delete-description="Pertanyaan yang sudah digunakan pada penilaian akan dinonaktifkan agar data jawaban tetap tersimpan.">@csrf @method('DELETE')<button class="danger-button" type="submit">Hapus</button></form></div>
        </x-admin.panel>
    </div>
</x-layouts.admin>
