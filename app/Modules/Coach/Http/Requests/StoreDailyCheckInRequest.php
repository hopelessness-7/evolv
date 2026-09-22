<?php

namespace App\Modules\Coach\Http\Requests;

use App\Modules\Coach\DTO\Input\StoreDailyCheckInData;
use Illuminate\Foundation\Http\FormRequest;

class StoreDailyCheckInRequest extends FormRequest
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
            'energy' => ['required', 'integer', 'min:1', 'max:5'],
            'focus' => ['required', 'integer', 'min:1', 'max:5'],
            'practice_ready' => ['required', 'integer', 'min:1', 'max:5'],
            'plan_date' => ['nullable', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function getDto(): StoreDailyCheckInData
    {
        $data = $this->validated();

        return new StoreDailyCheckInData(
            energy: (int) $data['energy'],
            focus: (int) $data['focus'],
            practiceReady: (int) $data['practice_ready'],
            planDate: isset($data['plan_date']) ? (string) $data['plan_date'] : null,
            note: isset($data['note']) ? (string) $data['note'] : null,
        );
    }
}
