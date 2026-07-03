<?php

namespace App\Modules\LearningPath\Enums;

enum StepStatus: string
{
    case Locked = 'locked';
    case Available = 'available';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
