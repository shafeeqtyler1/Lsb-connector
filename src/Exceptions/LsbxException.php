<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Exceptions;

use Exception;

class LsbxException extends Exception
{
    protected ?string $errorType;
    protected ?string $errorCode;
    protected ?string $errorDetails;
    protected ?int $httpStatusCode;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?string $errorType = null,
        ?string $errorCode = null,
        ?string $errorDetails = null,
        ?int $httpStatusCode = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorType = $errorType;
        $this->errorCode = $errorCode;
        $this->errorDetails = $errorDetails;
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getErrorType(): ?string
    {
        return $this->errorType;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getErrorDetails(): ?string
    {
        return $this->errorDetails;
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * Create exception from API error response
     */
    public static function fromApiResponse(array $response, int $httpStatusCode): self
    {
        return new self(
            message: $response['message'] ?? 'Unknown API error',
            code: $httpStatusCode,
            errorType: $response['type'] ?? null,
            errorCode: $response['code'] ?? null,
            errorDetails: $response['details'] ?? null,
            httpStatusCode: $httpStatusCode
        );
    }
}
