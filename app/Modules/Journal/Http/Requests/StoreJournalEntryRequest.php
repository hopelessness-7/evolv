<?php

namespace App\Modules\Journal\Http\Requests;

use App\Modules\Journal\Enums\EntryKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJournalEntryRequest extends FormRequest
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
            'body' => ['required', 'string', 'min:1', 'max:10000'],
            'node_slug' => ['nullable', 'string', 'max:128'],
            'plan_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function kind(): EntryKind
    {
        $raw = $this->validated('kind') ?? EntryKind::Note->value;

        return EntryKind::from((string) $raw);
    }
}
