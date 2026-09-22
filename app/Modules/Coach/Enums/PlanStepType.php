<?php

namespace App\Modules\Coach\Enums;

enum PlanStepType: string
{
    case Onboarding = 'onboarding';
    case CheckIn = 'check_in';
    case Lesson = 'lesson';
    case Practice = 'practice';
    case Mind = 'mind';
    case Reflection = 'reflection';
    case QuizReview = 'quiz_review';
    case Explore = 'explore';
}
