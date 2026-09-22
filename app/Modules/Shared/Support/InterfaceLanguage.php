<?php

namespace App\Modules\Shared\Support;

/**
 * Resolves the learner-facing interface language from onboarding facets.
 * Course/curriculum content is authored manually and is out of scope.
 */
final class InterfaceLanguage
{
    public const RU = 'ru';

    public const EN = 'en';

    /**
     * @param  array<string, mixed>|null  $profileSummary  Coach profile_summary shape
     */
    public static function fromProfileSummary(?array $profileSummary): string
    {
        return self::fromFacets(is_array($profileSummary['facets'] ?? null) ? $profileSummary['facets'] : null);
    }

    /**
     * @param  array<string, mixed>|null  $facets  user_profiles.facets
     */
    public static function fromFacets(?array $facets): string
    {
        $raw = $facets['core']['interface_language'] ?? self::RU;

        if (! is_string($raw) || $raw === '') {
            return self::RU;
        }

        // "both" → prefer Russian for generated UI until true bilingual shell exists
        return $raw === self::EN ? self::EN : self::RU;
    }

    public static function isRussian(string $language): bool
    {
        return $language !== self::EN;
    }

    /** Human name for LLM instructions. */
    public static function displayName(string $language): string
    {
        return $language === self::EN ? 'English' : 'Russian';
    }

    public static function llmPlanLanguageRule(string $language): string
    {
        $name = self::displayName($language);

        return "CRITICAL: Write greeting, every step title/description, and every reminder message entirely in {$name}. "
            .'Do not mix languages. Do not use English if the language is Russian.';
    }

    /**
     * @param  array<string, array{ru: string, en: string}>  $catalog
     */
    public static function pick(string $language, array $catalog, string $key, string $fallback = ''): string
    {
        $entry = $catalog[$key] ?? null;

        if ($entry === null) {
            return $fallback;
        }

        return $language === self::EN ? $entry['en'] : $entry['ru'];
    }
}
