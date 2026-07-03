<?php

namespace App\Modules\Coach\Enums;

enum PlanStepType: string
{
    case Onboarding = 'onboarding';
    case Lesson = 'lesson';
    case Practice = 'practice';
    case Mind = 'mind';
    case Reflection = 'reflection';
    case QuizReview = 'quiz_review';
    case Explore = 'explore';
}
