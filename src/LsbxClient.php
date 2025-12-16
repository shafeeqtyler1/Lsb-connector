<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector;

use Shafeeq\LsbConnector\Cache\CacheInterface;
use Shafeeq\LsbConnector\Cache\FileCache;
use Shafeeq\LsbConnector\Http\HttpClient;
use Shafeeq\LsbConnector\Resources\Customers;
use Shafeeq\LsbConnector\Resources\Accounts;
use Shafeeq\LsbConnector\Resources\Entities;
use Shafeeq\LsbConnector\Resources\Transfers;
use Shafeeq\LsbConnector\Resources\Webhooks;
use Shafeeq\LsbConnector\Exceptions\ConfigurationException;

/**
 * LSBX API Client
 *
 * Main entry point for interacting with the LSBX API.
 *
 * @link https://docs.lsbxapi.com/
 *
 * Usage:
 * ```php
 * $client = new LsbxClient('your_client_id', 'your_client_secret');
 *
 * // Create a customer
 * $customer = $client->customers()->createPerson($request);
 *
 * // Create an account
 * $account = $client->accounts()->create($request);
 *
 * // Send ACH transfer
 * $transfer = $client->transfers()->createAch($request);
 * ```
 */
class LsbxClient
{
    private Config $config;
    private HttpClient $httpClient;
    private ?Customers $customers = null;
    private ?Accounts $accounts = null;
    private ?Entities $entities = null;
    private ?Transfers $transfers = null;
    private ?Webhooks $webhooks = null;

    /**
     * Create a new LSBX API client
     *
     * @param string $clientId Your LSBX client ID
     * @param string $clientSecret Your LSBX client secret
     * @param bool $sandbox Use sandbox environment (default: true)
     * @param array $options Additional options:
     *   - 'base_url': Custom base URL (optional)
     *   - 'auth_url': Custom auth URL (optional)
     *   - 'timeout': Request timeout in seconds (default: 300)
     *   - 'cache_dir': Custom cache directory (optional)
     *   - 'cache': Custom CacheInterface implementation (optional)
     *   - 'logger': Callable for logging requests/responses (optional)
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        bool $sandbox = true,
        array $options = []
    ) {
        if (empty($clientId) || empty($clientSecret)) {
            throw ConfigurationException::missingCredentials();
        }

        $this->config = new Config(
            clientId: $clientId,
            clientSecret: $clientSecret,
            sandbox: $sandbox,
            baseUrl: $options['base_url'] ?? null,
            authUrl: $options['auth_url'] ?? null,
            timeout: $options['timeout'] ?? Config::DEFAULT_TIMEOUT,
            cacheDir: $options['cache_dir'] ?? null
        );

        $cache = $options['cache'] ?? new FileCache($this->config->getCacheDir());

        $this->httpClient = new HttpClient($this->config, $cache);

        if (isset($options['logger']) && is_callable($options['logger'])) {
            $this->httpClient->setLogger($options['logger']);
        }
    }

    /**
     * Create client for sandbox environment
     */
    public static function sandbox(string $clientId, string $clientSecret, array $options = []): self
    {
        return new self($clientId, $clientSecret, true, $options);
    }

    /**
     * Create client for production environment
     */
    public static function production(string $clientId, string $clientSecret, array $options = []): self
    {
        return new self($clientId, $clientSecret, false, $options);
    }

    /**
     * Get the Customers resource
     */
    public function customers(): Customers
    {
        if ($this->customers === null) {
            $this->customers = new Customers($this->httpClient);
        }

        return $this->customers;
    }

    /**
     * Get the Accounts resource
     */
    public function accounts(): Accounts
    {
        if ($this->accounts === null) {
            $this->accounts = new Accounts($this->httpClient);
        }

        return $this->accounts;
    }

    /**
     * Get the Entities resource
     */
    public function entities(): Entities
    {
        if ($this->entities === null) {
            $this->entities = new Entities($this->httpClient);
        }

        return $this->entities;
    }

    /**
     * Get the Transfers resource
     */
    public function transfers(): Transfers
    {
        if ($this->transfers === null) {
            $this->transfers = new Transfers($this->httpClient);
        }

        return $this->transfers;
    }

    /**
     * Get the Webhooks resource
     */
    public function webhooks(): Webhooks
    {
        if ($this->webhooks === null) {
            $this->webhooks = new Webhooks($this->httpClient);
        }

        return $this->webhooks;
    }

    /**
     * Get the current access token
     */
    public function getAccessToken(): string
    {
        return $this->httpClient->getAccessToken();
    }

    /**
     * Clear the cached access token
     */
    public function clearAccessToken(): void
    {
        $this->httpClient->clearAccessToken();
    }

    /**
     * Get the configuration
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Check if using sandbox environment
     */
    public function isSandbox(): bool
    {
        return $this->config->isSandbox();
    }

    /**
     * Set a logger for requests/responses
     */
    public function setLogger(callable $logger): self
    {
        $this->httpClient->setLogger($logger);
        return $this;
    }
}
