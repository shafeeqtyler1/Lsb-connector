<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Exceptions;

class AuthenticationException extends LsbxException
{
    public static function failedToRetrieveToken(string $reason = ''): self
    {
        $message = 'Failed to retrieve access token from LSBX API';
        if ($reason) {
            $message .= ': ' . $reason;
        }
        return new self($message, 401);
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid client credentials', 401);
    }

    public static function tokenExpired(): self
    {
        return new self('Access token has expired', 401);
    }
}
