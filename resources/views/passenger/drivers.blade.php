<x-passenger.layout title="Pilih Driver" variant="driver-list">
    <header class="passenger-mobile-header">
        <a href="{{ route('passenger.rating.vehicle', $vehicle->qr_token) }}" aria-label="Kembali"><x-lucide-chevron-left aria-hidden="true" /></a>
        <h1>Pilih Driver</h1>
        <p>Pilih driver yang bertugas melayani Anda saat ini.</p>
    </header>

    <section class="passenger-driver-page">
        @if($drivers->isNotEmpty())
            <div class="passenger-driver-search-panel">
                <label class="passenger-driver-search" for="driver-search">
                    <x-lucide-search aria-hidden="true" />
                    <input id="driver-search" type="search" placeholder="Cari nama driver..." autocomplete="off" enterkeyhint="search" data-driver-search>
                    <button type="button" data-driver-search-clear hidden aria-label="Hapus pencarian"><x-lucide-x aria-hidden="true" /></button>
                </label>
                <p><span data-driver-result-count>{{ $drivers->count() }}</span> driver tersedia</p>
            </div>
        @endif

        <div class="passenger-driver-list">
            @forelse($drivers as $driver)
                <a class="passenger-driver-option" href="{{ route('passenger.rating.driver', [$vehicle->qr_token, $driver]) }}" data-driver-option data-search="{{ Str::lower($driver->full_name.' '.$driver->nickname.' '.$driver->branch?->name) }}">
                    <div class="passenger-driver-photo">
                        <x-entity-photo type="driver" :src="$driver->photo" :alt="$driver->full_name" />
                    </div>
                    <div class="passenger-driver-summary">
                        <h2>{{ $driver->full_name }}</h2>
                        <small>{{ $driver->branch?->name ?: 'Driver aktif' }}</small>
                        @if ($driver->passenger_average_rating !== null)
                            <p><x-lucide-star aria-hidden="true" /> {{ number_format($driver->passenger_average_rating, 1) }} <span>Penilaian</span></p>
                        @else
                            <p class="passenger-driver-empty-rating">Belum ada rating</p>
                        @endif
                    </div>
                    <span class="passenger-driver-select-label">Pilih <x-lucide-chevron-right aria-hidden="true" /></span>
                </a>
            @empty
                <div class="passenger-driver-empty">Belum ada driver aktif pada cabang kendaraan ini.</div>
            @endforelse
            @if($drivers->isNotEmpty())
                <div class="passenger-driver-empty passenger-driver-search-empty" data-driver-search-empty hidden><x-lucide-search-x aria-hidden="true" /><strong>Driver tidak ditemukan</strong><span>Coba gunakan nama driver yang berbeda.</span></div>
            @endif
        </div>

        <aside class="passenger-driver-help"><x-lucide-info aria-hidden="true" /><p>Pastikan driver yang dipilih sesuai dengan perjalanan Anda.</p></aside>
    </section>

    @if($drivers->isNotEmpty())
        <script>
            (() => {
                const input = document.querySelector('[data-driver-search]');
                const clearButton = document.querySelector('[data-driver-search-clear]');
                const resultCount = document.querySelector('[data-driver-result-count]');
                const emptyState = document.querySelector('[data-driver-search-empty]');
                const options = [...document.querySelectorAll('[data-driver-option]')];
                const normalize = (value) => value.toLocaleLowerCase('id-ID').normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();

                const filterDrivers = () => {
                    const query = normalize(input.value);
                    let visible = 0;

                    options.forEach((option) => {
                        const matches = normalize(option.dataset.search || '').includes(query);
                        option.hidden = !matches;
                        visible += matches ? 1 : 0;
                    });

                    resultCount.textContent = visible;
                    clearButton.hidden = query === '';
                    emptyState.hidden = visible !== 0;
                };

                input.addEventListener('input', filterDrivers);
                clearButton.addEventListener('click', () => {
                    input.value = '';
                    filterDrivers();
                    input.focus();
                });
            })();
        </script>
    @endif
</x-passenger.layout>
