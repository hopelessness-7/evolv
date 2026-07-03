<?php

namespace App\Modules\Practice\Contracts;

use App\Modules\Practice\DTO\ExecutionResultData;

interface CodeExecutionDriverInterface
{
    public function execute(
        string $sourceCode,
        int $languageId,
        ?string $stdin = null,
        ?string $expectedOutput = null,
    ): ExecutionResultData;
}
