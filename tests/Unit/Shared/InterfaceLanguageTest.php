<?php

namespace Tests\Unit\Shared;

use App\Modules\Shared\Support\InterfaceLanguage;
use PHPUnit\Framework\TestCase;

class InterfaceLanguageTest extends TestCase
{
    public function test_defaults_to_russian(): void
    {
        $this->assertSame(InterfaceLanguage::RU, InterfaceLanguage::fromProfileSummary(null));
        $this->assertSame(InterfaceLanguage::RU, InterfaceLanguage::fromFacets(null));
        $this->assertSame(InterfaceLanguage::RU, InterfaceLanguage::fromFacets(['core' => ['interface_language' => 'both']]));
    }

    public function test_respects_english(): void
    {
        $this->assertSame(
            InterfaceLanguage::EN,
            InterfaceLanguage::fromProfileSummary([
                'facets' => ['core' => ['interface_language' => 'en']],
            ]),
        );
    }

    public function test_pick_returns_localized_string(): void
    {
        $catalog = [
            'hi' => ['ru' => 'Привет', 'en' => 'Hello'],
        ];

        $this->assertSame('Привет', InterfaceLanguage::pick(InterfaceLanguage::RU, $catalog, 'hi'));
        $this->assertSame('Hello', InterfaceLanguage::pick(InterfaceLanguage::EN, $catalog, 'hi'));
    }
}
