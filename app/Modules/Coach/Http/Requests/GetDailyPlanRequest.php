<?php

namespace App\Modules\Coach\Http\Requests;

use App\Modules\Coach\DTO\Input\GetDailyPlanData;
use App\Modules\Shared\Http\Requests\DtoRequest;

class GetDailyPlanRequest extends DtoRequest
{
    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'refresh' => ['sometimes', 'boolean'],
        ];
    }

    public function getDto(): GetDailyPlanData
    {
        return $this->toDto(GetDailyPlanData::class);
    }
}
