<?php

namespace App\Modules\Practice\DTO\Input;

use App\Modules\Shared\Contracts\FromValidated;

final readonly class SubmitAttemptData implements FromValidated
{
    public function __construct(
        public string $code,
        public int $atomId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            code: (string) $validated['code'],
            atomId: (int) $validated['atom_id'],
        );
    }
}
