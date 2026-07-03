<?php

namespace App\Modules\Notifications\Http\Requests;

use App\Modules\Notifications\DTO\Input\ListNotificationsData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class ListNotificationsRequest extends DtoRequest
{
    public function rules(): array
    {
        return [
            'unread_only' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function getDto(): ListNotificationsData
    {
        return $this->toDto(ListNotificationsData::class);
    }
}
