<?php

namespace App\Modules\Curriculum\Enums;

enum EdgeKind: string
{
    case Requires = 'requires';
    case RelatedTo = 'related_to';
    case IsNewVersionOf = 'is_new_version_of';
}
