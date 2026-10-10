<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[0-9+()\-\s.]+$/'],
            'pickup_at' => ['required', 'date'],
            'flight_info' => ['nullable', 'string', 'max:300'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'pickup_city' => ['nullable', 'string', 'max:100'],
            'pickup_state' => ['nullable', 'string', 'max:100'],
            'pickup_zip' => ['nullable', 'string', 'max:20'],
            'dropoff_address' => ['required', 'string', 'max:255'],
            'passengers' => ['required', 'integer', 'min:1', 'max:99'],
            'luggage' => ['required', 'integer', 'min:0', 'max:99'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'source_path' => ['nullable', 'string', 'max:255'],
            'g-recaptcha-response' => ['nullable', 'string'],
            'website' => ['nullable', 'max:0'], // honeypot
        ];
    }

    public function attributes(): array
    {
        return [
            'pickup_at' => 'pick-up date and time',
            'pickup_address' => 'pick-up address',
            'dropoff_address' => 'drop-off address',
            'flight_info' => 'additional info',
        ];
    }
}
