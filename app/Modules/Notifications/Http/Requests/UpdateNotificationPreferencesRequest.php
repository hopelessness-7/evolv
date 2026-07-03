<?php

namespace App\Modules\Notifications\Http\Requests;

use App\Modules\Notifications\DTO\Input\UpdateNotificationPreferencesData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class UpdateNotificationPreferencesRequest extends DtoRequest
{
    public function rules(): array
    {
        return [
            'email_enabled' => ['required', 'boolean'],
        ];
    }

    public function getDto(): UpdateNotificationPreferencesData
    {
        return $this->toDto(UpdateNotificationPreferencesData::class);
    }
}
