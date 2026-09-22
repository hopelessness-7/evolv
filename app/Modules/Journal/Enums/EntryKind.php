<?php

namespace App\Modules\Journal\Enums;

enum EntryKind: string
{
    case Reflection = 'reflection';
    case Note = 'note';
    case Question = 'question';
}
