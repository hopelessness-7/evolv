<?php

namespace App\Modules\Onboarding\Enums;

enum Tier: string
{
    case Core = 'core';
    case Lite = 'lite';
    case Extended = 'extended';
}
