<?php

namespace App\Modules\AI\Enums;

enum GenerationJobStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
