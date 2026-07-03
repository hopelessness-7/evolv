<?php

namespace App\Modules\Notifications\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class NotificationsException extends ApiException
{
    public static function notFound(): self
    {
        return new self('Notification not found.', 404, 'notification_not_found');
    }
}
