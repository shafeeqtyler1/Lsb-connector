<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Shafeeq\LsbConnector\Config;
use Shafeeq\LsbConnector\Cache\CacheInterface;
use Shafeeq\LsbConnector\Cache\FileCache;
use Shafeeq\LsbConnector\Exceptions\ApiException;
use Shafeeq\LsbConnector\Exceptions\AuthenticationException;
use Shafeeq\LsbConnector\Exceptions\LsbxException;
use Ramsey\Uuid\Uuid;

class HttpClient
{
    private Client $client;
    private Config $config;
    private CacheInterface $cache;
    private ?string $accessToken = null;
    private ?callable $logger = null;

    public function __construct(Config $config, ?CacheInterface $cache = null)
    {
        $this->config = $config;
        $this->cache = $cache ?? new FileCache($config->getCacheDir());

        $this->client = new Client([
            'timeout' => $config->getTimeout(),
            'http_errors' => false,
        ]);
    }

    /**
     * Set a logger callback for request/response logging
     */
    public function setLogger(?callable $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Generate a UUID v4 for idempotency keys
     */
    public function generateIdempotencyKey(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Get the access token (fetches new one if needed)
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $cacheKey = 'lsbx_access_token_' . md5($this->config->getClientId());
        $token = $this->cache->get($cacheKey);

        if ($token !== null) {
            $this->accessToken = $token;
            return $this->accessToken;
        }

        $tokenData = $this->fetchAccessToken();
        $this->accessToken = $tokenData['access_token'];

        $this->cache->set($cacheKey, $this->accessToken, Config::TOKEN_CACHE_TTL);

        return $this->accessToken;
    }

    /**
     * Clear the cached access token
     */
    public function clearAccessToken(): void
    {
        $cacheKey = 'lsbx_access_token_' . md5($this->config->getClientId());
        $this->cache->delete($cacheKey);
        $this->accessToken = null;
    }

    /**
     * Fetch a new access token from the auth server
     */
    private function fetchAccessToken(): array
    {
        $authUrl = $this->config->getAuthUrl() . '/oauth2/token';

        try {
            $response = $this->client->request('POST', $authUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config->getClientId(),
                    'client_secret' => $this->config->getClientSecret(),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || !isset($data['access_token'])) {
                throw AuthenticationException::failedToRetrieveToken(
                    $data['message'] ?? 'Invalid response'
                );
            }

            return $data;
        } catch (GuzzleException $e) {
            throw AuthenticationException::failedToRetrieveToken($e->getMessage());
        }
    }

    /**
     * Send an API request
     */
    public function request(
        string $method,
        string $endpoint,
        ?array $data = null,
        array $headers = [],
        array $query = []
    ): Response {
        $url = $this->config->getBaseUrl() . '/' . ltrim($endpoint, '/');

        $options = [
            'headers' => $this->buildHeaders($headers),
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        if ($data !== null) {
            $options['body'] = json_encode($data);
        }

        try {
            $guzzleResponse = $this->client->request($method, $url, $options);

            $response = new Response(
                $guzzleResponse->getStatusCode(),
                $guzzleResponse->getHeaders(),
                $guzzleResponse->getBody()->getContents()
            );

            $this->log($method, $endpoint, $data, $response);

            // Handle authentication errors
            if ($response->getStatusCode() === 401) {
                $this->clearAccessToken();
                throw AuthenticationException::tokenExpired();
            }

            // Handle other errors
            if (!$response->isSuccessful()) {
                $this->handleErrorResponse($response);
            }

            return $response;
        } catch (GuzzleException $e) {
            throw ApiException::requestFailed($e->getMessage());
        }
    }

    /**
     * Send a GET request
     */
    public function get(string $endpoint, array $query = [], array $headers = []): Response
    {
        return $this->request('GET', $endpoint, null, $headers, $query);
    }

    /**
     * Send a POST request
     */
    public function post(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Send a PATCH request
     */
    public function patch(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->request('PATCH', $endpoint, $data, $headers);
    }

    /**
     * Send a DELETE request
     */
    public function delete(string $endpoint, ?array $data = null, array $headers = []): Response
    {
        return $this->request('DELETE', $endpoint, $data, $headers);
    }

    /**
     * Build request headers
     */
    private function buildHeaders(array $customHeaders = []): array
    {
        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return array_merge($defaultHeaders, $customHeaders);
    }

    /**
     * Handle error responses
     */
    private function handleErrorResponse(Response $response): void
    {
        $data = $response->getData() ?? [];
        $statusCode = $response->getStatusCode();

        throw LsbxException::fromApiResponse($data, $statusCode);
    }

    /**
     * Log request/response if logger is set
     */
    private function log(string $method, string $endpoint, ?array $data, Response $response): void
    {
        if ($this->logger !== null) {
            call_user_func($this->logger, [
                'method' => $method,
                'endpoint' => $endpoint,
                'request_data' => $data,
                'response_status' => $response->getStatusCode(),
                'response_data' => $response->getData(),
            ]);
        }
    }
}
