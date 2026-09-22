<?php

namespace App\Modules\LearningPath\Http\Requests;

use App\Modules\Curriculum\Enums\Track;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackRequest extends FormRequest
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
            'track' => ['required', 'string', Rule::enum(Track::class)],
        ];
    }

    public function track(): Track
    {
        return Track::from((string) $this->validated('track'));
    }
}
