<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,mobile'],
            'email' => ['nullable', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'house_flat_no' => ['required', 'string', 'max:100'],
            'building_apartment_name' => ['nullable', 'string', 'max:150'],
            'street_address' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'area' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'digits:6'],
            'delivery_instructions' => ['nullable', 'string', 'max:500'],
            'referral_code' => ['nullable', 'string', 'exists:customer_profiles,referral_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
            'mobile.unique' => 'This mobile number is already registered.',
            'pincode.digits' => 'Pincode must be exactly 6 digits.',
            'referral_code.exists' => 'The provided referral code is invalid.',
        ];
    }
}
