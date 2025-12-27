<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Exceptions;

class ValidationException extends LsbxException
{
    protected array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function requiredField(string $field): self
    {
        return new self("The {$field} field is required", [$field => "The {$field} field is required"]);
    }

    public static function invalidFormat(string $field, string $expectedFormat): self
    {
        return new self(
            "The {$field} field has invalid format. Expected: {$expectedFormat}",
            [$field => "Invalid format, expected: {$expectedFormat}"]
        );
    }

    public static function maxLength(string $field, int $maxLength): self
    {
        return new self(
            "The {$field} field must not exceed {$maxLength} characters",
            [$field => "Must not exceed {$maxLength} characters"]
        );
    }
}
