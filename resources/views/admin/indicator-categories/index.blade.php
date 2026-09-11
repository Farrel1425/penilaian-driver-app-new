<x-layouts.admin title="Kategori Indikator">
    <x-slot:pageActions>
        <form class="master-filter-panel" method="GET" action="{{ route('admin.indicator-categories.index') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label><span>TARGET</span><select name="target_type" onchange="this.form.requestSubmit()" aria-label="Filter target"><option value="">Semua Target</option>@foreach ([App\Models\Question::TARGET_DRIVER, App\Models\Question::TARGET_VEHICLE, App\Models\Question::TARGET_FEEDBACK] as $target)<option value="{{ $target }}" @selected(request('target_type') === $target)>{{ App\Models\Question::targetLabel($target) }}</option>@endforeach</select></label>
            <label><span>STATUS</span><select name="status" onchange="this.form.requestSubmit()" aria-label="Filter status kategori"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select></label>
            @if (request()->filled('search') || request()->filled('target_type') || request()->filled('status'))<a class="secondary-button assessment-reset-button" href="{{ route('admin.indicator-categories.index') }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>@endif
            <a class="primary-button master-create-button" href="{{ route('admin.indicator-categories.create') }}"><x-lucide-plus aria-hidden="true" /><span>Tambah Kategori Indikator</span></a>
        </form>
    </x-slot:pageActions>

    <section class="branch-list-card master-table-card">
        <div class="table-wrap branch-table-wrap">
            <table class="data-table branch-data-table">
                <thead><tr><th>KATEGORI INDIKATOR</th><th>TARGET</th><th class="branch-column-count">PERTANYAAN</th><th>URUTAN</th><th class="branch-column-status">STATUS</th><th class="branch-column-actions">AKSI</th></tr></thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td><span class="question-target-badge question-target-{{ $category->target_type }}">{{ App\Models\Question::targetLabel($category->target_type) }}</span></td>
                            <td class="branch-cell-count">{{ $category->questions_count }}</td>
                            <td class="question-cell-center">{{ $category->sort_order }}</td>
                            <td class="branch-cell-status"><span class="driver-status driver-status-{{ $category->status }}">{{ $category->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="branch-cell-actions"><div class="table-row-actions"><a href="{{ route('admin.indicator-categories.edit', $category) }}" aria-label="Edit kategori {{ $category->name }}" title="Edit"><x-lucide-pencil aria-hidden="true" /></a><form method="POST" action="{{ route('admin.indicator-categories.toggle-status', $category) }}" data-confirm data-no-loading data-confirm-title="{{ $category->status === 'active' ? 'Nonaktifkan kategori?' : 'Aktifkan kategori?' }}" data-confirm-description="Kategori nonaktif tidak dapat dipilih pada pertanyaan baru." data-confirm-label="{{ $category->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}" data-confirm-tone="primary" data-confirm-icon="power">@csrf @method('PATCH')<button type="submit" aria-label="Ubah status kategori {{ $category->name }}" title="{{ $category->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}"><x-lucide-power aria-hidden="true" /></button></form><form method="POST" action="{{ route('admin.indicator-categories.destroy', $category) }}" data-delete-confirm data-no-loading data-delete-name="Kategori {{ $category->name }}" data-delete-description="Kategori yang masih dipakai pertanyaan akan dinonaktifkan.">@csrf @method('DELETE')<button type="submit" aria-label="Hapus kategori {{ $category->name }}" title="Hapus"><x-lucide-trash-2 aria-hidden="true" /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty-state title="Belum ada kategori indikator" description="Tambahkan kategori indikator untuk digunakan pada master pertanyaan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="branch-pagination"><span>Menampilkan {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori</span>@if ($categories->hasPages())<x-admin.pagination :paginator="$categories" label="Pagination kategori indikator" />@endif</footer>
    </section>
</x-layouts.admin>
