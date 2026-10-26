<?php

namespace App\Livewire\Forms;

use App\Enums\ComplaintCategory;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Case fields of a complaint. The citizen form and the chatbot both validate with these rules,
 * so a complaint is checked the same way however it is filed. Files live on the component.
 */
class ComplaintForm extends Form
{
    public $court_id = null;
    public string $case_number = '';
    public string $case_title = '';
    public string $category = '';
    public string $description = '';
    public bool $declaration = false;

    /** Honeypot — real users never see or fill this. */
    public string $website = '';

    public function rules(): array
    {
        return static::rulesFor();
    }

    public static function rulesFor(): array
    {
        return [
            'court_id' => ['required', 'integer', Rule::exists('courts', 'id')->where('is_active', true)],
            'case_number' => ['required', 'string', 'max:50', 'regex:/^[\pL\pN\s\/\-.#()]+$/u'],
            'case_title' => ['required', 'string', 'min:3', 'max:200'],
            'category' => ['required', Rule::enum(ComplaintCategory::class)],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'declaration' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    public function validationAttributes(): array
    {
        return static::attributeNames();
    }

    public static function attributeNames(): array
    {
        return [
            'court_id' => __('court'),
            'case_number' => __('case number'),
            'case_title' => __('case title'),
            'category' => __('complaint category'),
            'description' => __('complaint details'),
            'declaration' => __('declaration'),
        ];
    }

    public function messages(): array
    {
        return static::messageTexts();
    }

    public static function messageTexts(): array
    {
        return [
            'case_number.regex' => __('Use letters, numbers, spaces and / - . # only, e.g. 123/2025.'),
            'description.min' => __('Please describe the problem in a little more detail (at least 20 characters).'),
            'declaration.accepted' => __('Please confirm the information is correct.'),
        ];
    }

    /** Validated data ready for ComplaintSubmissionService. */
    public function payload(): array
    {
        return static::normalize($this->validate());
    }

    public static function normalize(array $data): array
    {
        unset($data['declaration'], $data['website']);

        $data['court_id'] = (int) $data['court_id'];
        $data['case_number'] = strtoupper(trim(preg_replace('/\s+/u', ' ', $data['case_number'])));
        $data['case_title'] = trim(preg_replace('/\s+/u', ' ', $data['case_title']));
        $data['description'] = trim($data['description']);

        return $data;
    }
}
