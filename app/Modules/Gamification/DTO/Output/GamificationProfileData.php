<?php

namespace App\Modules\Gamification\DTO\Output;

use App\Modules\Gamification\Models\GamificationProfile;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class GamificationProfileData implements RespondsAsArray
{
    /**
     * @param  list<string>  $unlockedPieces
     */
    public function __construct(
        public int $xp,
        public int $currentStreak,
        public int $longestStreak,
        public ?string $lastActiveOn,
        public array $unlockedPieces,
    ) {}

    public static function fromModel(GamificationProfile $profile): self
    {
        $pieces = $profile->unlocked_pieces ?? [];

        return new self(
            xp: (int) $profile->xp,
            currentStreak: (int) $profile->current_streak,
            longestStreak: (int) $profile->longest_streak,
            lastActiveOn: $profile->last_active_on?->toDateString(),
            unlockedPieces: array_values(array_filter(
                is_array($pieces) ? $pieces : [],
                fn ($piece) => is_string($piece) && $piece !== '',
            )),
        );
    }

    public function toArray(): array
    {
        return [
            'xp' => $this->xp,
            'current_streak' => $this->currentStreak,
            'longest_streak' => $this->longestStreak,
            'last_active_on' => $this->lastActiveOn,
            'unlocked_pieces' => $this->unlockedPieces,
        ];
    }
}
