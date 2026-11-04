<?php

namespace App\Http\Requests;

use App\Enums\IncidentSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'service' => ['required', 'string', 'max:80'],
            'severity' => ['required', new Enum(IncidentSeverity::class)],
            'owner' => ['required', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:1000'],
        ];
    }
}
