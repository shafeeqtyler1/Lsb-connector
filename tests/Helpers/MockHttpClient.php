<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests\Helpers;

use ShafeeqKt\LsbConnector\Http\HttpClient;
use ShafeeqKt\LsbConnector\Http\Response;
use ShafeeqKt\LsbConnector\Config;
use ShafeeqKt\LsbConnector\Cache\ArrayCache;

class MockHttpClient extends HttpClient
{
    private array $mockResponses = [];
    private array $requestHistory = [];
    private int $responseIndex = 0;

    public function __construct()
    {
        $config = new Config(
            clientId: 'test_client_id',
            clientSecret: 'test_client_secret',
            sandbox: true
        );

        parent::__construct($config, new ArrayCache());
    }

    /**
     * Add a mock response
     */
    public function addMockResponse(int $statusCode, array $data, array $headers = []): self
    {
        $this->mockResponses[] = new Response(
            $statusCode,
            $headers,
            json_encode($data)
        );

        return $this;
    }

    /**
     * Add multiple mock responses
     */
    public function addMockResponses(array $responses): self
    {
        foreach ($responses as $response) {
            $this->addMockResponse(
                $response['status'] ?? 200,
                $response['data'] ?? [],
                $response['headers'] ?? []
            );
        }

        return $this;
    }

    /**
     * Override the request method to return mock responses
     */
    public function request(
        string $method,
        string $endpoint,
        ?array $data = null,
        array $headers = [],
        array $query = []
    ): Response {
        $this->requestHistory[] = [
            'method' => $method,
            'endpoint' => $endpoint,
            'data' => $data,
            'headers' => $headers,
            'query' => $query,
        ];

        if (isset($this->mockResponses[$this->responseIndex])) {
            return $this->mockResponses[$this->responseIndex++];
        }

        // Default success response
        return new Response(200, [], json_encode(['success' => true]));
    }

    /**
     * Override get to use our mock request
     */
    public function get(string $endpoint, array $query = [], array $headers = []): Response
    {
        return $this->request('GET', $endpoint, null, $headers, $query);
    }

    /**
     * Override post to use our mock request
     */
    public function post(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Override patch to use our mock request
     */
    public function patch(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->request('PATCH', $endpoint, $data, $headers);
    }

    /**
     * Override delete to use our mock request
     */
    public function delete(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->request('DELETE', $endpoint, $data, $headers);
    }

    /**
     * Get the request history
     */
    public function getRequestHistory(): array
    {
        return $this->requestHistory;
    }

    /**
     * Get the last request
     */
    public function getLastRequest(): ?array
    {
        return end($this->requestHistory) ?: null;
    }

    /**
     * Clear request history and mock responses
     */
    public function reset(): self
    {
        $this->mockResponses = [];
        $this->requestHistory = [];
        $this->responseIndex = 0;

        return $this;
    }

    /**
     * Override getAccessToken to avoid actual API calls
     */
    public function getAccessToken(): string
    {
        return 'mock_access_token';
    }
}
