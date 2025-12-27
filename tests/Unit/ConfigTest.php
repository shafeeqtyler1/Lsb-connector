<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Tests\Unit;

use Shafeeq\LsbConnector\Tests\TestCase;
use Shafeeq\LsbConnector\Config;

class ConfigTest extends TestCase
{
    public function test_sandbox_config_uses_sandbox_urls(): void
    {
        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true
        );

        $this->assertEquals(Config::SANDBOX_BASE_URL, $config->getBaseUrl());
        $this->assertEquals(Config::SANDBOX_AUTH_URL, $config->getAuthUrl());
        $this->assertTrue($config->isSandbox());
    }

    public function test_production_config_uses_production_urls(): void
    {
        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: false
        );

        $this->assertEquals(Config::PRODUCTION_BASE_URL, $config->getBaseUrl());
        $this->assertEquals(Config::PRODUCTION_AUTH_URL, $config->getAuthUrl());
        $this->assertFalse($config->isSandbox());
    }

    public function test_custom_urls_override_defaults(): void
    {
        $customBaseUrl = 'https://custom-api.example.com';
        $customAuthUrl = 'https://custom-auth.example.com';

        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true,
            baseUrl: $customBaseUrl,
            authUrl: $customAuthUrl
        );

        $this->assertEquals($customBaseUrl, $config->getBaseUrl());
        $this->assertEquals($customAuthUrl, $config->getAuthUrl());
    }

    public function test_custom_urls_strips_trailing_slashes(): void
    {
        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true,
            baseUrl: 'https://api.example.com/',
            authUrl: 'https://auth.example.com/'
        );

        $this->assertEquals('https://api.example.com', $config->getBaseUrl());
        $this->assertEquals('https://auth.example.com', $config->getAuthUrl());
    }

    public function test_returns_credentials(): void
    {
        $config = new Config(
            clientId: 'my_client_id',
            clientSecret: 'my_client_secret',
            sandbox: true
        );

        $this->assertEquals('my_client_id', $config->getClientId());
        $this->assertEquals('my_client_secret', $config->getClientSecret());
    }

    public function test_default_timeout(): void
    {
        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true
        );

        $this->assertEquals(Config::DEFAULT_TIMEOUT, $config->getTimeout());
    }

    public function test_custom_timeout(): void
    {
        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true,
            timeout: 60
        );

        $this->assertEquals(60, $config->getTimeout());
    }

    public function test_cache_dir(): void
    {
        $cacheDir = '/tmp/custom_cache';

        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true,
            cacheDir: $cacheDir
        );

        $this->assertEquals($cacheDir, $config->getCacheDir());
    }

    public function test_null_cache_dir_by_default(): void
    {
        $config = new Config(
            clientId: 'test_id',
            clientSecret: 'test_secret',
            sandbox: true
        );

        $this->assertNull($config->getCacheDir());
    }
}
