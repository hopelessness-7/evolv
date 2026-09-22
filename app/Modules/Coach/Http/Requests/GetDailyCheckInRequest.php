<?php

namespace App\Modules\Coach\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDailyCheckInRequest extends FormRequest
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
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function dateQuery(): ?string
    {
        $date = $this->validated('date') ?? $this->query('date');

        return is_string($date) && $date !== '' ? $date : null;
    }
}
