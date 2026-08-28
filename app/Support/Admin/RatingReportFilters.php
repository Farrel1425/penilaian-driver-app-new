<?php

namespace App\Support\Admin;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class RatingReportFilters
{
    public function __construct(
        public readonly ?CarbonImmutable $startDate,
        public readonly ?CarbonImmutable $endDate,
        public readonly ?int $branchId,
        public readonly ?int $driverId,
        public readonly ?int $vehicleId,
        public readonly ?string $search,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $timezone = config('app.display_timezone', 'Asia/Makassar');
        $start = $request->filled('start_date') ? CarbonImmutable::parse($request->input('start_date'), $timezone)->startOfDay()->utc() : null;
        $end = $request->filled('end_date') ? CarbonImmutable::parse($request->input('end_date'), $timezone)->endOfDay()->utc() : null;

        return new self(
            $start,
            $end,
            $request->integer('branch_id') ?: null,
            $request->integer('driver_id') ?: null,
            $request->integer('vehicle_id') ?: null,
            $request->string('search')->trim()->value() ?: null,
        );
    }

    public function queryString(): array
    {
        return array_filter([
            'start_date' => $this->startDate?->timezone(config('app.display_timezone'))->toDateString(),
            'end_date' => $this->endDate?->timezone(config('app.display_timezone'))->toDateString(),
            'branch_id' => $this->branchId,
            'driver_id' => $this->driverId,
            'vehicle_id' => $this->vehicleId,
            'search' => $this->search,
        ], fn ($value) => filled($value));
    }
}
