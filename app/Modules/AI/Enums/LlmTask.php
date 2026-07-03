<?php

namespace App\Modules\AI\Enums;

enum LlmTask: string
{
    case Embed = 'embed';
    case Chat = 'chat';
    case CodeReview = 'code_review';
    case Coach = 'coach';
    case PsychSession = 'psych_session';
    case SpecReview = 'spec_review';
    case DailyPlan = 'daily_plan';
    case StructuredJson = 'structured_json';
}
