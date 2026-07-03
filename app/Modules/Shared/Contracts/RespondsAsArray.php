<?php

namespace App\Modules\Shared\Contracts;

interface RespondsAsArray
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
