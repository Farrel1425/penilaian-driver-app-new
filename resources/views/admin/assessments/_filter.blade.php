<form class="filter-bar admin-dashboard-filters assessment-filter" method="GET" data-auto-filter-form>
    <input type="hidden" name="search" value="{{ $filters->search }}">
    <label><span>Mulai</span><input type="date" name="start_date" value="{{ $filters->startDate?->toDateString() }}" aria-label="Tanggal mulai"></label>
    <label><span>Sampai</span><input type="date" name="end_date" value="{{ $filters->endDate?->toDateString() }}" aria-label="Tanggal selesai"></label>
    <label><span>Unit Kerja</span><select name="branch_id" aria-label="Unit kerja"><option value="">Semua Unit Kerja</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected($filters->branchId === $branch->id)>{{ $branch->name }}</option>@endforeach</select></label>
    @if (isset($drivers))<label><span>Driver</span><select name="driver_id" aria-label="Driver"><option value="">Semua Driver</option>@foreach($drivers as $driver)<option value="{{ $driver->id }}" @selected($filters->driverId === $driver->id)>{{ $driver->full_name }}</option>@endforeach</select></label>@endif
    @if (isset($vehicles))<label><span>Kendaraan</span><select name="vehicle_id" aria-label="Kendaraan"><option value="">Semua Kendaraan</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}" @selected($filters->vehicleId === $vehicle->id)>{{ $vehicle->police_number }} - {{ $vehicle->brand }}</option>@endforeach</select></label>@endif

    <a class="secondary-button assessment-reset-button" href="{{ url()->current() }}"><x-lucide-rotate-ccw aria-hidden="true" /><span>Reset</span></a>
</form>
