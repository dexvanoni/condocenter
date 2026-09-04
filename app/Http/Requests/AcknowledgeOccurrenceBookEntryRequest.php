<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\OccurrenceBookEntry;

class AcknowledgeOccurrenceBookEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entry = $this->route('entry');

        return $entry && $this->user()?->can('acknowledge', $entry);
    }

    public function rules(): array
    {
        return [
            'acknowledgment_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
