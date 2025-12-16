<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Resources;

use Shafeeq\LsbConnector\Http\HttpClient;
use Shafeeq\LsbConnector\Http\Response;

abstract class AbstractResource
{
    protected HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Generate an idempotency key
     */
    protected function generateIdempotencyKey(): string
    {
        return $this->httpClient->generateIdempotencyKey();
    }

    /**
     * Add idempotency header
     */
    protected function withIdempotency(?string $idempotencyKey = null): array
    {
        return [
            'Idempotency-Key' => $idempotencyKey ?? $this->generateIdempotencyKey(),
        ];
    }

    /**
     * Send GET request
     */
    protected function get(string $endpoint, array $query = [], array $headers = []): Response
    {
        return $this->httpClient->get($endpoint, $query, $headers);
    }

    /**
     * Send POST request
     */
    protected function post(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->httpClient->post($endpoint, $data, $headers);
    }

    /**
     * Send PATCH request
     */
    protected function patch(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->httpClient->patch($endpoint, $data, $headers);
    }

    /**
     * Send DELETE request
     */
    protected function delete(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->httpClient->delete($endpoint, $data, $headers);
    }
}
