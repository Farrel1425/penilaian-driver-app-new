<x-layouts.admin title="Monitoring Penilaian">
    @include('admin.partials.report-filter', ['filters' => $filters, 'branches' => $branches])
    <x-admin.panel>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Tanggal</th><th>Driver</th><th>Kendaraan</th><th>Cabang</th><th>Rating</th><th>Detail</th></tr></thead><tbody>
            @forelse($ratings as $rating)
                <tr><td>{{ $rating->submitted_at?->timezone(config('app.display_timezone'))?->format('d M Y H:i') }}</td><td>{{ $rating->driver?->full_name }}</td><td>{{ $rating->vehicle?->police_number }}</td><td>{{ $rating->branch?->name }}</td><td>{{ $analytics->ratingScore($rating) ?? '-' }}</td><td>{{ $rating->answers->count() }} jawaban</td></tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state title="Belum ada penilaian" description="Data monitoring akan muncul setelah penumpang mengirim penilaian." /></td></tr>
            @endforelse
        </tbody></table></div>{{ $ratings->links() }}
    </x-admin.panel>
</x-layouts.admin>
