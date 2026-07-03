<?php

namespace App\Modules\Onboarding\Repositories;

use App\Modules\Onboarding\Contracts\QuestionnaireRepositoryInterface;
use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\Tier;
use App\Modules\Onboarding\Models\Questionnaire;
use Illuminate\Support\Collection;

class QuestionnaireRepository implements QuestionnaireRepositoryInterface
{
    public function findById(int $id): ?Questionnaire
    {
        return Questionnaire::query()->find($id);
    }

    public function findCurrentByCode(string $code): ?Questionnaire
    {
        return Questionnaire::query()
            ->where('code', $code)
            ->where('is_current', true)
            ->first();
    }

    public function listCurrent(?Pillar $pillar = null, ?Tier $tier = null): Collection
    {
        return Questionnaire::query()
            ->where('is_current', true)
            ->when($pillar, fn ($q) => $q->where('pillar', $pillar->value))
            ->when($tier, fn ($q) => $q->where('tier', $tier->value))
            ->orderBy('code')
            ->get();
    }

    public function findByCodeAndVersion(string $code, string $version): ?Questionnaire
    {
        return Questionnaire::query()
            ->where('code', $code)
            ->where('version', $version)
            ->first();
    }
}
