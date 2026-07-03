<?php

namespace App\Modules\Content\Enums;

enum AtomKind: string
{
    case Theory = 'theory';
    case Snippet = 'snippet';
    case Quiz = 'quiz';
    case Exercise = 'exercise';
    case Summary = 'summary';
}
