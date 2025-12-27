<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector;

class Config
{
    public const SANDBOX_BASE_URL = 'https://lsbxsandboxapi.com';
    public const SANDBOX_AUTH_URL = 'https://auth.lsbxsandboxapi.com';
    public const PRODUCTION_BASE_URL = 'https://lsbxapi.com';
    public const PRODUCTION_AUTH_URL = 'https://auth.lsbxapi.com';

    public const TOKEN_CACHE_TTL = 800; // seconds (token expires in 900s, refresh at 800s)
    public const DEFAULT_TIMEOUT = 300; // seconds

    private string $clientId;
    private string $clientSecret;
    private bool $sandbox;
    private string $baseUrl;
    private string $authUrl;
    private int $timeout;
    private ?string $cacheDir;

    public function __construct(
        string $clientId,
        string $clientSecret,
        bool $sandbox = true,
        ?string $baseUrl = null,
        ?string $authUrl = null,
        int $timeout = self::DEFAULT_TIMEOUT,
        ?string $cacheDir = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->sandbox = $sandbox;
        $this->timeout = $timeout;
        $this->cacheDir = $cacheDir;

        // Set URLs based on environment or custom URLs
        if ($baseUrl !== null) {
            $this->baseUrl = rtrim($baseUrl, '/');
        } else {
            $this->baseUrl = $sandbox ? self::SANDBOX_BASE_URL : self::PRODUCTION_BASE_URL;
        }

        if ($authUrl !== null) {
            $this->authUrl = rtrim($authUrl, '/');
        } else {
            $this->authUrl = $sandbox ? self::SANDBOX_AUTH_URL : self::PRODUCTION_AUTH_URL;
        }
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getCacheDir(): ?string
    {
        return $this->cacheDir;
    }
}
