<?php

namespace App\Modules\Coach\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class GetDailyPlanData implements FromValidated
{
    public function __construct(
        public ?string $date,
        public bool $refresh,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            date: isset($validated['date']) ? (string) $validated['date'] : null,
            refresh: (bool) ($validated['refresh'] ?? false),
        );
    }
}
