<x-layouts.admin title="Riwayat Penilaian">
    <x-admin.panel class="assessment-list-panel">
        <div class="assessment-list-toolbar">
            @include('admin.assessments._filter', compact('filters', 'branches', 'drivers', 'vehicles'))
            <a class="primary-button assessment-export-button" data-no-loading href="{{ route('admin.assessments.export', $filters->queryString()) }}"><x-lucide-download aria-hidden="true" /><span>Export Excel</span></a>
        </div>
        <div class="table-wrap"><table class="data-table assessment-data-table"><thead><tr><th>Tanggal &amp; Jam</th><th>Unit Kerja</th><th>Kendaraan</th><th>Driver</th><th>Rating</th><th>Komentar</th><th>Aksi</th></tr></thead><tbody>
            @forelse ($ratings as $rating)
                @php($comments = $rating->answers->filter(fn ($answer) => filled($answer->answer_text))->pluck('answer_text')->filter())
                <tr><td><strong>{{ $rating->submitted_at?->timezone(config('app.display_timezone'))?->format('d M Y') }}</strong><small>{{ $rating->submitted_at?->timezone(config('app.display_timezone'))?->format('H:i') }}</small></td><td>{{ $rating->branch?->name ?? '-' }}</td><td><strong>{{ $rating->vehicle?->police_number ?? '-' }}</strong><small>{{ trim(($rating->vehicle?->brand ?? '').' '.($rating->vehicle?->model ?? '')) }}</small></td><td>{{ $rating->driver?->full_name ?? '-' }}</td><td><span class="assessment-rating"><x-lucide-star aria-hidden="true" />{{ $analytics->ratingScore($rating) ?? '-' }}</span></td><td><span class="assessment-comment">{{ $comments->isNotEmpty() ? str($comments->implode(' | '))->limit(56) : 'Tidak ada komentar' }}</span></td><td><a class="table-action action-view assessment-view-action" href="{{ route('admin.assessments.show', $rating) }}" aria-label="Detail penilaian" title="Lihat detail"><x-lucide-eye aria-hidden="true" /></a></td></tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state title="Belum ada penilaian" description="Riwayat akan muncul setelah penumpang mengirim penilaian." /></td></tr>
            @endforelse
        </tbody></table></div>
        <footer class="assessment-pagination">
            <span>Menampilkan {{ $ratings->firstItem() ?? 0 }} - {{ $ratings->lastItem() ?? 0 }} dari {{ $ratings->total() }} penilaian</span>
            @if ($ratings->hasPages())
                <nav aria-label="Pagination riwayat penilaian">
                    @if ($ratings->onFirstPage())
                        <span class="assessment-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>
                    @else
                        <a class="assessment-page-button" href="{{ $ratings->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
                    @endif
                    <span class="assessment-page-button is-current" aria-current="page">{{ $ratings->currentPage() }}</span>
                    @if ($ratings->hasMorePages())
                        <a class="assessment-page-button" href="{{ $ratings->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
                    @else
                        <span class="assessment-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>
                    @endif
                </nav>
            @endif
        </footer>
    </x-admin.panel>
</x-layouts.admin>
