<?php

namespace App\Modules\Onboarding\Contracts;

use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\Tier;
use App\Modules\Onboarding\Models\Questionnaire;
use Illuminate\Support\Collection;

interface QuestionnaireRepositoryInterface
{
    public function findById(int $id): ?Questionnaire;

    public function findCurrentByCode(string $code): ?Questionnaire;

  /**
     * @return Collection<int, Questionnaire>
     */
    public function listCurrent(?Pillar $pillar = null, ?Tier $tier = null): Collection;

    public function findByCodeAndVersion(string $code, string $version): ?Questionnaire;
}
