<?php

namespace App\Modules\Coach\Services;

use App\Modules\Coach\Enums\PlanStepType;
use App\Modules\Shared\Support\InterfaceLanguage;

class CheckInStepBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $lang): array
    {
        return [
            'type' => PlanStepType::CheckIn->value,
            'title' => InterfaceLanguage::pick($lang, [
                'check_in_title' => [
                    'ru' => 'Чек-ин дня',
                    'en' => 'Daily check-in',
                ],
            ], 'check_in_title'),
            'description' => InterfaceLanguage::pick($lang, [
                'check_in_desc' => [
                    'ru' => 'Три короткие шкалы — подстроим объём плана под самочувствие (не клиника).',
                    'en' => 'Three short scales — we will tune today\'s load (not a clinical assessment).',
                ],
            ], 'check_in_desc'),
            'minutes' => 1,
            'pillar' => null,
            'scales' => [
                [
                    'key' => 'energy',
                    'min' => 1,
                    'max' => 5,
                    'label' => InterfaceLanguage::pick($lang, [
                        'scale_energy' => [
                            'ru' => 'Энергия',
                            'en' => 'Energy',
                        ],
                    ], 'scale_energy'),
                ],
                [
                    'key' => 'focus',
                    'min' => 1,
                    'max' => 5,
                    'label' => InterfaceLanguage::pick($lang, [
                        'scale_focus' => [
                            'ru' => 'Фокус',
                            'en' => 'Focus',
                        ],
                    ], 'scale_focus'),
                ],
                [
                    'key' => 'practice_ready',
                    'min' => 1,
                    'max' => 5,
                    'label' => InterfaceLanguage::pick($lang, [
                        'scale_practice' => [
                            'ru' => 'Готовность к практике',
                            'en' => 'Ready for practice',
                        ],
                    ], 'scale_practice'),
                ],
            ],
            'tools' => [
                [
                    'type' => 'check_in',
                    'label' => InterfaceLanguage::pick($lang, [
                        'tool_check_in' => [
                            'ru' => 'Отправить чек-ин',
                            'en' => 'Submit check-in',
                        ],
                    ], 'tool_check_in'),
                ],
            ],
        ];
    }
}
