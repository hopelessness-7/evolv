<?php

namespace App\Modules\Coach\Enums;

enum DailyPlanStatus: string
{
    case Ready = 'ready';
    case Generating = 'generating';
}
