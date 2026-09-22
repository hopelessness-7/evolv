<?php

namespace App\Modules\Journal\Http\Requests;

use App\Modules\Journal\Enums\EntryKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kind' => ['sometimes', 'string', Rule::enum(EntryKind::class)],
            'body' => ['sometimes', 'string', 'min:1', 'max:10000'],
            'node_slug' => ['sometimes', 'nullable', 'string', 'max:128'],
            'plan_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function kind(): ?EntryKind
    {
        if (! $this->exists('kind')) {
            return null;
        }

        return EntryKind::from((string) $this->validated('kind'));
    }
}
