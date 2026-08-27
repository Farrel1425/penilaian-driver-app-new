<?php

namespace App\Http\Requests\Passenger;

use Illuminate\Foundation\Http\FormRequest;

class StorePassengerNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passenger_name' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return ['passenger_name' => 'nama Anda'];
    }
}
