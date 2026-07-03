<?php

namespace Database\Seeders;

use App\Modules\Onboarding\Models\Questionnaire;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class OnboardingQuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        $directory = database_path('seeders/data/onboarding');

        foreach (File::glob($directory.'/*.json') as $path) {
            $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            Questionnaire::query()->updateOrCreate(
                [
                    'code' => $payload['code'],
                    'version' => $payload['version'],
                ],
                [
                    'pillar' => $payload['pillar'],
                    'tier' => $payload['tier'],
                    'title' => $payload['title'],
                    'description' => $payload['description'] ?? null,
                    'schema' => $payload['schema'],
                    'prompt_templates' => $payload['prompt_templates'] ?? null,
                    'is_current' => true,
                    'published_at' => now(),
                ],
            );

            Questionnaire::query()
                ->where('code', $payload['code'])
                ->where('version', '!=', $payload['version'])
                ->update(['is_current' => false]);
        }
    }
}
