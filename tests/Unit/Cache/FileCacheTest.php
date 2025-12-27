<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Tests\Unit\Cache;

use Shafeeq\LsbConnector\Tests\TestCase;
use Shafeeq\LsbConnector\Cache\FileCache;

class FileCacheTest extends TestCase
{
    private string $cacheDir;
    private FileCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/lsbx_test_cache_' . uniqid();
        $this->cache = new FileCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        // Clean up cache directory
        $this->cache->clear();
        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
        parent::tearDown();
    }

    public function test_set_and_get(): void
    {
        $this->cache->set('test_key', 'test_value');

        $value = $this->cache->get('test_key');

        $this->assertEquals('test_value', $value);
    }

    public function test_get_returns_default_when_not_found(): void
    {
        $value = $this->cache->get('nonexistent_key', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    public function test_get_returns_null_by_default_when_not_found(): void
    {
        $value = $this->cache->get('nonexistent_key');

        $this->assertNull($value);
    }

    public function test_has_returns_true_when_key_exists(): void
    {
        $this->cache->set('existing_key', 'some_value');

        $this->assertTrue($this->cache->has('existing_key'));
    }

    public function test_has_returns_false_when_key_not_exists(): void
    {
        $this->assertFalse($this->cache->has('nonexistent_key'));
    }

    public function test_delete_removes_key(): void
    {
        $this->cache->set('key_to_delete', 'value');
        $this->assertTrue($this->cache->has('key_to_delete'));

        $result = $this->cache->delete('key_to_delete');

        $this->assertTrue($result);
        $this->assertFalse($this->cache->has('key_to_delete'));
    }

    public function test_delete_returns_true_for_nonexistent_key(): void
    {
        $result = $this->cache->delete('nonexistent_key');

        $this->assertTrue($result);
    }

    public function test_ttl_expires_value(): void
    {
        $this->cache->set('expiring_key', 'value', 1);

        // Value should exist immediately
        $this->assertTrue($this->cache->has('expiring_key'));
        $this->assertEquals('value', $this->cache->get('expiring_key'));

        // Wait for expiration
        sleep(2);

        // Value should be expired
        $this->assertFalse($this->cache->has('expiring_key'));
        $this->assertNull($this->cache->get('expiring_key'));
    }

    public function test_can_store_arrays(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $this->cache->set('array_key', $data);

        $retrieved = $this->cache->get('array_key');

        $this->assertEquals($data, $retrieved);
    }

    public function test_can_store_objects(): void
    {
        $object = new \stdClass();
        $object->name = 'Test';
        $object->value = 123;

        $this->cache->set('object_key', $object);

        $retrieved = $this->cache->get('object_key');

        $this->assertEquals($object->name, $retrieved->name);
        $this->assertEquals($object->value, $retrieved->value);
    }

    public function test_clear_removes_all_cached_items(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $this->cache->clear();

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    public function test_creates_cache_directory_if_not_exists(): void
    {
        $newCacheDir = sys_get_temp_dir() . '/new_cache_dir_' . uniqid();

        $this->assertFalse(is_dir($newCacheDir));

        $cache = new FileCache($newCacheDir);

        $this->assertTrue(is_dir($newCacheDir));

        // Cleanup
        $cache->clear();
        rmdir($newCacheDir);
    }

    public function test_uses_system_temp_dir_by_default(): void
    {
        $cache = new FileCache();

        $cache->set('test', 'value');
        $this->assertEquals('value', $cache->get('test'));

        $cache->clear();
    }
}
