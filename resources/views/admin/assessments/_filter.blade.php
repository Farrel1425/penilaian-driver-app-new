<form class="filter-bar report-filter assessment-filter" method="GET" data-debounced-search-form>
    <div class="search-field"><x-lucide-search aria-hidden="true" /><input type="search" name="search" value="{{ $filters->search }}" placeholder="Cari driver, kendaraan, atau unit kerja" data-debounced-search></div>
    <input type="date" name="start_date" value="{{ $filters->startDate?->toDateString() }}" aria-label="Tanggal mulai">
    <input type="date" name="end_date" value="{{ $filters->endDate?->toDateString() }}" aria-label="Tanggal selesai">
    <select name="branch_id" aria-label="Unit kerja"><option value="">Semua Unit Kerja</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected($filters->branchId === $branch->id)>{{ $branch->name }}</option>@endforeach</select>
    @if (isset($drivers))<select name="driver_id" aria-label="Driver"><option value="">Semua Driver</option>@foreach($drivers as $driver)<option value="{{ $driver->id }}" @selected($filters->driverId === $driver->id)>{{ $driver->full_name }}</option>@endforeach</select>@endif
    @if (isset($vehicles))<select name="vehicle_id" aria-label="Kendaraan"><option value="">Semua Kendaraan</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}" @selected($filters->vehicleId === $vehicle->id)>{{ $vehicle->police_number }} - {{ $vehicle->brand }}</option>@endforeach</select>@endif
    <button class="secondary-button" type="submit">Terapkan</button>
    <a class="secondary-button assessment-reset-button" href="{{ url()->current() }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
</form>
