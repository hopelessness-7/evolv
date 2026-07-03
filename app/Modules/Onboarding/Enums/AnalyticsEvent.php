<?php

namespace App\Modules\Onboarding\Enums;

enum AnalyticsEvent: string
{
    case QuestionnaireViewed = 'questionnaire_viewed';
    case SessionStarted = 'session_started';
    case SessionResumed = 'session_resumed';
    case AnswersSaved = 'answers_saved';
    case SessionCompleted = 'session_completed';
    case SessionAbandoned = 'session_abandoned';
    case StatusViewed = 'status_viewed';
}
