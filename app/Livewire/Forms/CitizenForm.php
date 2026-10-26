<?php

namespace App\Livewire\Forms;

use App\Models\Citizen;
use App\Support\Countries;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

/**
 * Citizen registration. identityRules() are also what the chatbot checks the citizen's name,
 * country, WhatsApp number and CNIC/passport with.
 */
class CitizenForm extends Form
{
    public string $name = '';
    public string $country = '';
    public string $phone = '';
    public string $cnic_passport = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            ...static::identityRules(),
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('citizens', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /** Rules for already-normalised values (see normalize()). */
    public static function identityRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\s.\'\-]+$/u'],
            'country' => ['required', Rule::in(Countries::codes())],
            'phone' => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
            'cnic_passport' => ['required', 'max:20', 'regex:/^(\d{5}-\d{7}-\d|(?=.*[A-Z])[A-Z0-9]{6,12})$/'],
        ];
    }

    public function validationAttributes(): array
    {
        return static::attributeNames();
    }

    public static function attributeNames(): array
    {
        return [
            'name' => __('full name'),
            'country' => __('country of residence'),
            'phone' => __('WhatsApp number'),
            'cnic_passport' => __('CNIC / passport number'),
            'email' => __('email'),
            'password' => __('password'),
        ];
    }

    public function messages(): array
    {
        return static::messageTexts();
    }

    public static function messageTexts(): array
    {
        return [
            'name.regex' => __('The name may only contain letters and spaces.'),
            'phone.regex' => __('Enter the number with its country code, e.g. +447700900123.'),
            'cnic_passport.regex' => __('Enter a CNIC/NICOP like 12345-1234567-1 or a passport number like AB1234567.'),
        ];
    }

    /** Tidies what people type before it is validated: spaces, dashes, 00 prefixes, lower case. */
    public static function normalize(string $field, string $value): string
    {
        return match ($field) {
            'name' => trim(preg_replace('/\s+/u', ' ', $value)),
            'phone' => Citizen::normalizePhone($value),
            'cnic_passport' => Citizen::normalizeIdentity($value),
            'email' => strtolower(trim($value)),
            default => trim($value),
        };
    }

    public function normalizeAll(): void
    {
        foreach (['name', 'phone', 'cnic_passport', 'email'] as $field) {
            $this->{$field} = static::normalize($field, $this->{$field});
        }
    }
}
