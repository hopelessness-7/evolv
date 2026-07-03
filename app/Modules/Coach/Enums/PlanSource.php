<?php

namespace App\Modules\Coach\Enums;

enum PlanSource: string
{
    case Llm = 'llm';
    case Fallback = 'fallback';
}
