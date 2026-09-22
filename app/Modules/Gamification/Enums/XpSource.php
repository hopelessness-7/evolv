<?php

namespace App\Modules\Gamification\Enums;

enum XpSource: string
{
    case StepComplete = 'step_complete';
    case QuizCorrect = 'quiz_correct';
    case PracticeAccepted = 'practice_accepted';
}
