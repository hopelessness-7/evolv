<?php

namespace App\Modules\Practice\Enums;

enum AttemptVerdict: string
{
    case Accepted = 'accepted';
    case WrongAnswer = 'wrong_answer';
    case RuntimeError = 'runtime_error';
    case CompileError = 'compile_error';
    case Timeout = 'timeout';
    case JudgeUnavailable = 'judge_unavailable';
}
