<?php

namespace App\Modules\Auth\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class AuthException extends ApiException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials.', 401, 'invalid_credentials');
    }

    public static function emailTaken(): self
    {
        return new self('Email is already registered.', 422, 'email_taken');
    }
}
