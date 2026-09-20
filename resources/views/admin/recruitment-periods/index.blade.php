<x-layouts.admin title="Periode Recruitment">
    <x-slot:pageActions><a class="primary-button master-create-button" href="{{ route('admin.recruitment-periods.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Periode</span></a></x-slot:pageActions>
    <section class="branch-list-card master-table-card">
        <div class="table-wrap">
            <table class="data-table recruitment-admin-table">
                <thead><tr><th>PERIODE</th><th>RENTANG TANGGAL</th><th>LOWONGAN</th><th>STATUS</th><th>AKSI</th></tr></thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td><strong>{{ $period->name }}</strong></td>
                            <td>{{ $period->starts_at?->format('d M Y') ?? '-' }} &mdash; {{ $period->ends_at?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $period->vacancies_count }} lowongan</td>
                            <td><span class="driver-status driver-status-{{ $period->is_active ? 'active' : 'inactive' }}">{{ $period->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="table-row-actions"><a href="{{ route('admin.recruitment-periods.edit', $period) }}" aria-label="Edit periode {{ $period->name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.recruitment-periods.destroy', $period) }}" data-delete-confirm data-no-loading data-delete-name="Periode {{ $period->name }}" data-delete-description="Periode yang sudah memiliki lowongan tidak dapat dihapus.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus periode {{ $period->name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty-state title="Belum ada periode recruitment" description="Tambahkan periode sebelum membuat lowongan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $periods->firstItem() ?? 0 }} - {{ $periods->lastItem() ?? 0 }} dari {{ $periods->total() }} periode</span>@if ($periods->hasPages())<x-admin.pagination :paginator="$periods" label="Pagination periode recruitment" />@endif</footer>
    </section>
</x-layouts.admin>
