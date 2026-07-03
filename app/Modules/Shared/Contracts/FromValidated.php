<?php

namespace App\Modules\Shared\Contracts;

interface FromValidated
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): static;
}
