<?php

namespace App\Modules\Curriculum\Enums;

enum NodeStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
