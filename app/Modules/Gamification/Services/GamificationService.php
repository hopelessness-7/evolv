<?php

namespace App\Modules\Gamification\Services;

use App\Models\User;
use App\Modules\Gamification\Contracts\GamificationProfileRepositoryInterface;
use App\Modules\Gamification\Contracts\GamificationReaderInterface;
use App\Modules\Gamification\Contracts\GamificationRecorderInterface;
use App\Modules\Gamification\Contracts\XpLedgerRepositoryInterface;
use App\Modules\Gamification\DTO\Output\GamificationProfileData;
use App\Modules\Gamification\Enums\XpSource;
use App\Modules\Gamification\Models\GamificationProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService implements GamificationReaderInterface, GamificationRecorderInterface
{
    public function __construct(
        private readonly GamificationProfileRepositoryInterface $profiles,
        private readonly XpLedgerRepositoryInterface $ledger,
    ) {}

    public function getProfile(User $user): GamificationProfileData
    {
        return $this->getOrCreateProfile($user);
    }

    public function getOrCreateProfile(User $user): GamificationProfileData
    {
        $profile = $this->profiles->findForUser($user)
            ?? $this->profiles->createForUser($user);

        return GamificationProfileData::fromModel($profile);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  list<string>  $puzzlePieces
     */
    public function awardXp(
        User $user,
        XpSource $source,
        array $meta = [],
        array $puzzlePieces = [],
    ): GamificationProfileData {
        $amount = (int) config('gamification.xp.'.$source->value, 0);

        if ($amount <= 0) {
            return $this->getOrCreateProfile($user);
        }

        $piecesFromMeta = $meta['puzzle_pieces'] ?? [];
        $pieces = array_values(array_unique(array_filter(
            array_merge(
                $puzzlePieces,
                is_array($piecesFromMeta) ? $piecesFromMeta : [],
            ),
            fn ($piece) => is_string($piece) && $piece !== '',
        )));

        $profile = DB::transaction(function () use ($user, $source, $amount, $meta, $pieces): GamificationProfile {
            $profile = $this->profiles->findForUser($user)
                ?? $this->profiles->createForUser($user);

            $profile->xp = (int) $profile->xp + $amount;
            $this->applyStreak($profile);
            $this->mergeUnlockedPieces($profile, $pieces);

            $this->profiles->save($profile);
            $this->ledger->append($user, $source, $amount, $meta !== [] ? $meta : null);

            return $profile->fresh() ?? $profile;
        });

        return GamificationProfileData::fromModel($profile);
    }

    private function applyStreak(GamificationProfile $profile): void
    {
        $today = Carbon::today();
        $lastActive = $profile->last_active_on;

        if ($lastActive !== null && $lastActive->isSameDay($today)) {
            return;
        }

        if ($lastActive !== null && $lastActive->isSameDay($today->copy()->subDay())) {
            $profile->current_streak = (int) $profile->current_streak + 1;
        } else {
            $profile->current_streak = 1;
        }

        $profile->longest_streak = max(
            (int) $profile->longest_streak,
            (int) $profile->current_streak,
        );
        $profile->last_active_on = $today;
    }

    /**
     * @param  list<string>  $pieces
     */
    private function mergeUnlockedPieces(GamificationProfile $profile, array $pieces): void
    {
        if ($pieces === []) {
            return;
        }

        $existing = $profile->unlocked_pieces ?? [];
        if (! is_array($existing)) {
            $existing = [];
        }

        $profile->unlocked_pieces = array_values(array_unique(array_merge(
            array_values(array_filter($existing, fn ($piece) => is_string($piece) && $piece !== '')),
            $pieces,
        )));
    }
}
