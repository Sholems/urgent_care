<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHomecareEnquiryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9][0-9 .()\-]{5,30}$/'],
            'service' => ['required', Rule::in(array_keys(config('homecare.services')))],
            'town' => ['required', 'string', 'max:100'],
            'preferred_start' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'privacy_acknowledged' => ['accepted'],
        ];
    }
}
