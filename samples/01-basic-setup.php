<?php

/**
 * LSBX Connector SDK - Basic Setup Example
 *
 * This example demonstrates how to initialize and configure the SDK client.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Shafeeq\LsbConnector\LsbxClient;
use Shafeeq\LsbConnector\Exceptions\LsbxException;
use Shafeeq\LsbConnector\Exceptions\AuthenticationException;

// =============================================================================
// Method 1: Basic Initialization (Sandbox)
// =============================================================================

$client = new LsbxClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    sandbox: true
);

// =============================================================================
// Method 2: Using Static Factory Methods
// =============================================================================

// Sandbox environment
$sandboxClient = LsbxClient::sandbox('your_client_id', 'your_client_secret');

// Production environment
$productionClient = LsbxClient::production('your_client_id', 'your_client_secret');

// =============================================================================
// Method 3: With Custom Options
// =============================================================================

$clientWithOptions = new LsbxClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    sandbox: true,
    options: [
        // Request timeout in seconds (default: 30)
        'timeout' => 60,

        // Custom cache directory for token storage
        'cache_dir' => '/tmp/lsbx-cache',

        // Custom logger function for debugging
        'logger' => function (array $data) {
            $timestamp = date('Y-m-d H:i:s');
            $message = json_encode($data, JSON_PRETTY_PRINT);
            file_put_contents('/tmp/lsbx-debug.log', "[$timestamp] $message\n", FILE_APPEND);
        },
    ]
);

// =============================================================================
// Method 4: With Custom Cache Implementation (e.g., Redis)
// =============================================================================

use Shafeeq\LsbConnector\Cache\CacheInterface;

class RedisCache implements CacheInterface
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if ($ttl !== null) {
            return $this->redis->setex($key, $ttl, serialize($value));
        }
        return $this->redis->set($key, serialize($value));
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }
}

// Usage with Redis cache:
// $redis = new \Redis();
// $redis->connect('127.0.0.1', 6379);
//
// $clientWithRedis = new LsbxClient(
//     clientId: 'your_client_id',
//     clientSecret: 'your_client_secret',
//     sandbox: true,
//     options: ['cache' => new RedisCache($redis)]
// );

// =============================================================================
// Accessing Resources
// =============================================================================

// Access different API resources
$customers = $client->customers();
$accounts = $client->accounts();
$entities = $client->entities();
$transfers = $client->transfers();
$webhooks = $client->webhooks();

// Access configuration
$config = $client->getConfig();
echo "API URL: " . $config->getApiUrl() . "\n";
echo "Is Sandbox: " . ($config->isSandbox() ? 'Yes' : 'No') . "\n";

// =============================================================================
// Error Handling
// =============================================================================

try {
    $customers = $client->customers()->list();
} catch (AuthenticationException $e) {
    // Handle authentication errors (invalid credentials, expired token)
    echo "Authentication failed: " . $e->getMessage() . "\n";
} catch (LsbxException $e) {
    // Handle other SDK errors
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Setup complete!\n";
