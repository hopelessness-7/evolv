<?php

namespace App\Modules\Shared\Http\Requests;

use App\Modules\Shared\Contracts\FromValidated;
use Illuminate\Foundation\Http\FormRequest;

abstract class DtoRequest extends FormRequest
{
    abstract public function getDto(): object;

    /**
     * @template T of FromValidated
     *
     * @param  class-string<T>  $dtoClass
     * @return T
     */
    protected function toDto(string $dtoClass): object
    {
        /** @var T $dto */
        $dto = $dtoClass::fromValidated($this->validated());

        return $dto;
    }
}
