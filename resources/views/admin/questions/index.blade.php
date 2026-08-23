<x-layouts.admin title="Master Pertanyaan">
    <section class="question-list-card">
        @if ($isReordering)
            <div class="question-list-toolbar question-reorder-toolbar">
                <div class="question-reorder-notice">
                    <span><strong>Mode ubah urutan aktif</strong><small>Geser pertanyaan ke posisi yang Anda inginkan, lalu simpan.</small></span>
                </div>
                <div class="question-reorder-actions">
                    <a class="secondary-button" href="{{ route('admin.questions.index') }}">Batal</a>
                    <form method="POST" action="{{ route('admin.questions.reorder') }}" data-question-reorder-form>
                        @csrf
                        @method('PATCH')
                        <div data-question-order-inputs></div>
                        <button class="primary-button" type="submit" @disabled($questions->isEmpty())><x-lucide-save aria-hidden="true" /><span>Simpan Urutan</span></button>
                    </form>
                </div>
            </div>
        @else
            <form class="question-list-toolbar" method="GET" action="{{ route('admin.questions.index') }}" data-debounced-search-form>
                <div class="question-list-filters">
                    <label class="question-search-field">
                        <x-lucide-search aria-hidden="true" />
                        <input name="search" value="{{ request('search') }}" placeholder="Cari pertanyaan..." aria-label="Cari pertanyaan" data-debounced-search>
                    </label>

                    <select name="target_type" onchange="this.form.requestSubmit()" aria-label="Filter kategori">
                        <option value="">Semua Kategori</option>
                        <option value="driver" @selected(request('target_type') === 'driver')>Driver</option>
                        <option value="vehicle" @selected(request('target_type') === 'vehicle')>Kendaraan</option>
                    </select>

                    <select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Aktif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                </div>

                <div class="question-list-actions">
                    <a class="secondary-button question-reorder-button" href="{{ route('admin.questions.index', ['reorder' => 1]) }}"><x-lucide-grip-vertical aria-hidden="true" /><span>Ubah Urutan</span></a>
                    <a class="primary-button question-create-button" href="{{ route('admin.questions.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Pertanyaan</span></a>
                </div>
            </form>
        @endif

        <div class="table-wrap question-table-wrap">
            <table class="data-table question-data-table">
                <thead>
                    <tr>
                        <th class="question-column-number">No.</th>
                        <th>Pertanyaan</th>
                        <th>Kategori</th>
                        <th>Indikator</th>
                        <th>Tipe Jawaban</th>
                        <th>Wajib</th>
                        <th>Bobot</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th class="question-column-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody @if ($isReordering) data-question-reorder-list @endif>
                    @forelse($questions as $question)
                        <tr @class(['question-reorder-row' => $isReordering]) @if ($isReordering) data-question-row data-question-id="{{ $question->id }}" draggable="true" @endif>
                            <td class="question-cell-center question-order-number">
                                @if ($isReordering)<span class="question-drag-handle" aria-hidden="true"><x-lucide-grip-vertical /></span>@endif
                                <span data-question-position>{{ $isReordering ? $loop->iteration : $questions->firstItem() + $loop->index }}</span>
                            </td>
                            <td class="question-cell-question">{{ $question->question }}</td>
                            <td>
                                <span class="question-target-badge question-target-{{ $question->target_type }}">
                                    {{ $question->target_type === 'driver' ? 'Driver' : 'Kendaraan' }}
                                </span>
                            </td>
                            <td>{{ $question->indicator }}</td>
                            <td>
                                @if ($question->answer_type === 'rating')
                                    <span class="question-answer-rating"><x-lucide-star aria-hidden="true" />Rating (1-5)</span>
                                @else
                                    {{ str($question->answer_type)->replace('_', ' ')->title() }}
                                @endif
                            </td>
                            <td>
                                @if ($question->is_required)
                                    <span class="question-required-badge">Ya</span>
                                @else
                                    <span class="question-optional-label">Tidak</span>
                                @endif
                            </td>
                            <td class="question-cell-center">{{ $question->weight }}%</td>
                            <td class="question-cell-center"><span data-question-sort-order>{{ $question->sort_order }}</span></td>
                            <td>
                                <span class="question-status question-status-{{ $question->status }}">
                                    {{ $question->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                @if ($isReordering)
                                    <span class="question-reorder-row-status">Siap dipindahkan</span>
                                @else
                                    <div class="table-row-actions">
                                        <a href="{{ route('admin.questions.show', $question) }}" aria-label="Lihat pertanyaan: {{ $question->question }}" title="Lihat"><x-lucide-eye aria-hidden="true" /></a>
                                        <a href="{{ route('admin.questions.edit', $question) }}" aria-label="Edit pertanyaan: {{ $question->question }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a>
                                        <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" data-delete-confirm data-no-loading data-delete-name="Pertanyaan {{ $question->question }}" data-delete-description="Pertanyaan yang sudah digunakan pada penilaian akan dinonaktifkan agar data jawaban tetap tersimpan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus pertanyaan: {{ $question->question }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><x-admin.empty-state title="Belum ada pertanyaan" description="Tambahkan pertanyaan agar form penilaian dapat dibangun dari database." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="question-pagination">
            @if ($isReordering)
                <span>{{ $questions->count() }} pertanyaan ditampilkan dalam urutan global.</span>
            @else
                <span>Menampilkan {{ $questions->firstItem() ?? 0 }} - {{ $questions->lastItem() ?? 0 }} dari {{ $questions->total() }} data</span>
            @endif
            @if (! $isReordering && $questions->hasPages())
                <nav aria-label="Pagination pertanyaan">
                    @if ($questions->onFirstPage())
                        <span class="question-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>
                    @else
                        <a class="question-page-button" href="{{ $questions->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
                    @endif
                    <span class="question-page-button is-current">{{ $questions->currentPage() }}</span>
                    @if ($questions->hasMorePages())
                        <a class="question-page-button" href="{{ $questions->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
                    @else
                        <span class="question-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>
                    @endif
                </nav>
            @endif
        </footer>
    </section>
</x-layouts.admin>
