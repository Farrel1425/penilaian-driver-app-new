<x-layouts.admin title="Pengguna">
    <section class="user-list-card vehicle-list-card">
        <div class="user-list-toolbar vehicle-list-toolbar">
            <form method="GET" class="user-filter-form vehicle-list-filters">
                <label class="sr-only" for="user-search">Cari pengguna</label>
                <label class="vehicle-search-field" for="user-search"><x-lucide-search aria-hidden="true" /><input id="user-search" name="search" type="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."></label>
                <label class="sr-only" for="user-status">Status</label>
                <select id="user-status" name="status" onchange="this.form.submit()"><option value="">Semua Status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select>
            </form>
            <a href="{{ route('admin.users.create') }}" class="primary-button vehicle-create-button"><x-lucide-plus aria-hidden="true" /> Tambah Admin</a>
        </div>
        <div class="user-table-scroll">
            <table class="user-list-table">
                <thead><tr><th>No</th><th>Foto</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th class="user-actions-heading">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td><span class="user-photo">@if ($user->photo)<img src="{{ str_starts_with($user->photo, 'http') ? $user->photo : asset('storage/'.$user->photo) }}" alt="Foto {{ $user->name }}">@else{{ str($user->name)->substr(0, 1)->upper() }}@endif</span></td>
                            <td><strong>{{ $user->name }}</strong><small>Administrator sistem</small></td>
                            <td>{{ $user->email }}</td><td><span class="user-role-badge">Admin</span></td>
                            <td><span class="status-badge {{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'is-active' : 'is-inactive' }}">{{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="user-row-actions"><a href="{{ route('admin.users.show', $user) }}" title="Lihat detail {{ $user->name }}" aria-label="Lihat detail {{ $user->name }}"><x-lucide-eye /></a><a href="{{ route('admin.users.edit', $user) }}" title="Edit {{ $user->name }}" aria-label="Edit {{ $user->name }}"><x-lucide-pencil /></a><form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus akun admin ini?')">@csrf @method('DELETE')<button type="submit" title="Hapus {{ $user->name }}" aria-label="Hapus {{ $user->name }}"><x-lucide-trash-2 /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="user-empty-state">Belum ada akun admin yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <footer class="user-pagination">
                <span>Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data</span>
                <nav aria-label="Pagination pengguna">
                    @if ($users->onFirstPage())
                        <span class="driver-page-button is-disabled"><x-lucide-chevron-left aria-hidden="true" /></span>
                    @else
                        <a class="driver-page-button" href="{{ $users->previousPageUrl() }}" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
                    @endif
                    <span class="driver-page-button is-current">{{ $users->currentPage() }}</span>
                    @if ($users->hasMorePages())
                        <a class="driver-page-button" href="{{ $users->nextPageUrl() }}" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
                    @else
                        <span class="driver-page-button is-disabled"><x-lucide-chevron-right aria-hidden="true" /></span>
                    @endif
                </nav>
            </footer>
        @endif
    </section>
</x-layouts.admin>
