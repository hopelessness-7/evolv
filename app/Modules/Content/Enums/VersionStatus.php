<?php

namespace App\Modules\Content\Enums;

enum VersionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
