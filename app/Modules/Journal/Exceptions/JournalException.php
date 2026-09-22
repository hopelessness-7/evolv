<?php

namespace App\Modules\Journal\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class JournalException extends ApiException
{
    public static function notFound(int $id): self
    {
        return new self("Journal entry [{$id}] not found.", 404, 'journal_entry_not_found');
    }
}
