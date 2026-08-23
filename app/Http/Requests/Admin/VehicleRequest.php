<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'branch_id' => ['required', Rule::exists(Branch::class, 'id')],
            'police_number' => ['required', 'string', 'max:50', Rule::unique('vehicles', 'police_number')->ignore($vehicle?->id)],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'color' => ['nullable', 'string', 'max:100'],
            'chassis_number' => ['nullable', 'string', 'max:255'],
            'engine_number' => ['nullable', 'string', 'max:255'],
            'fuel_type' => ['nullable', Rule::in(array_keys(Vehicle::FUEL_TYPES))],
            'transmission' => ['nullable', Rule::in(array_keys(Vehicle::TRANSMISSION_TYPES))],
            'passenger_capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_source' => ['nullable', Rule::in(array_keys(Vehicle::ACQUISITION_SOURCES))],
            'ownership_type' => ['nullable', Rule::in([Vehicle::OWNERSHIP_COMPANY, Vehicle::OWNERSHIP_RENTAL])],
            'contract_number' => ['nullable', 'string', 'max:255'],
            'contract_expired_at' => ['nullable', 'date'],
            'stnk_expired_at' => ['nullable', 'date'],
            'kir_expired_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'interior_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['required', Rule::in([Vehicle::STATUS_ACTIVE, Vehicle::STATUS_INACTIVE])],
        ];
    }
}
