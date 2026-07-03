<?php

namespace App\Modules\LearningPath\Enums;

enum PlanStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
}
