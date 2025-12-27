<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Tests\Unit;

use Shafeeq\LsbConnector\Tests\TestCase;
use Shafeeq\LsbConnector\LsbxClient;
use Shafeeq\LsbConnector\Config;
use Shafeeq\LsbConnector\Resources\Customers;
use Shafeeq\LsbConnector\Resources\Accounts;
use Shafeeq\LsbConnector\Resources\Entities;
use Shafeeq\LsbConnector\Resources\Transfers;
use Shafeeq\LsbConnector\Resources\Webhooks;
use Shafeeq\LsbConnector\Exceptions\ConfigurationException;

class LsbxClientTest extends TestCase
{
    public function test_can_create_client_with_credentials(): void
    {
        $client = new LsbxClient(
            clientId: 'test_client_id',
            clientSecret: 'test_client_secret',
            sandbox: true
        );

        $this->assertInstanceOf(LsbxClient::class, $client);
        $this->assertTrue($client->isSandbox());
    }

    public function test_throws_exception_for_empty_client_id(): void
    {
        $this->expectException(ConfigurationException::class);

        new LsbxClient(
            clientId: '',
            clientSecret: 'test_secret'
        );
    }

    public function test_throws_exception_for_empty_client_secret(): void
    {
        $this->expectException(ConfigurationException::class);

        new LsbxClient(
            clientId: 'test_id',
            clientSecret: ''
        );
    }

    public function test_sandbox_factory_method(): void
    {
        $client = LsbxClient::sandbox('test_id', 'test_secret');

        $this->assertTrue($client->isSandbox());
    }

    public function test_production_factory_method(): void
    {
        $client = LsbxClient::production('test_id', 'test_secret');

        $this->assertFalse($client->isSandbox());
    }

    public function test_returns_customers_resource(): void
    {
        $client = new LsbxClient('test_id', 'test_secret');

        $customers = $client->customers();

        $this->assertInstanceOf(Customers::class, $customers);
        // Should return same instance on subsequent calls
        $this->assertSame($customers, $client->customers());
    }

    public function test_returns_accounts_resource(): void
    {
        $client = new LsbxClient('test_id', 'test_secret');

        $accounts = $client->accounts();

        $this->assertInstanceOf(Accounts::class, $accounts);
        $this->assertSame($accounts, $client->accounts());
    }

    public function test_returns_entities_resource(): void
    {
        $client = new LsbxClient('test_id', 'test_secret');

        $entities = $client->entities();

        $this->assertInstanceOf(Entities::class, $entities);
        $this->assertSame($entities, $client->entities());
    }

    public function test_returns_transfers_resource(): void
    {
        $client = new LsbxClient('test_id', 'test_secret');

        $transfers = $client->transfers();

        $this->assertInstanceOf(Transfers::class, $transfers);
        $this->assertSame($transfers, $client->transfers());
    }

    public function test_returns_webhooks_resource(): void
    {
        $client = new LsbxClient('test_id', 'test_secret');

        $webhooks = $client->webhooks();

        $this->assertInstanceOf(Webhooks::class, $webhooks);
        $this->assertSame($webhooks, $client->webhooks());
    }

    public function test_returns_config(): void
    {
        $client = new LsbxClient('test_id', 'test_secret');

        $config = $client->getConfig();

        $this->assertInstanceOf(Config::class, $config);
        $this->assertEquals('test_id', $config->getClientId());
    }

    public function test_accepts_custom_options(): void
    {
        $logCalled = false;
        $logger = function ($data) use (&$logCalled) {
            $logCalled = true;
        };

        $client = new LsbxClient(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true,
            options: [
                'timeout' => 60,
                'cache_dir' => sys_get_temp_dir() . '/lsbx_test',
                'logger' => $logger,
            ]
        );

        $this->assertInstanceOf(LsbxClient::class, $client);
    }
}
