<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Exceptions;

class ApiException extends LsbxException
{
    public static function badRequest(string $message, ?string $details = null): self
    {
        return new self(
            message: $message,
            code: 400,
            errorDetails: $details,
            httpStatusCode: 400
        );
    }

    public static function notFound(string $resource): self
    {
        return new self(
            message: "Resource not found: {$resource}",
            code: 404,
            httpStatusCode: 404
        );
    }

    public static function forbidden(string $message = 'Access forbidden'): self
    {
        return new self(
            message: $message,
            code: 403,
            httpStatusCode: 403
        );
    }

    public static function serverError(string $message = 'Internal server error'): self
    {
        return new self(
            message: $message,
            code: 500,
            httpStatusCode: 500
        );
    }

    public static function requestFailed(string $message): self
    {
        return new self(
            message: $message,
            code: 0
        );
    }
}
