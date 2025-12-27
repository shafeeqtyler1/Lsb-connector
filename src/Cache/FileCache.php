<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Cache;

use ShafeeqKt\LsbConnector\Exceptions\ConfigurationException;

class FileCache implements CacheInterface
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/lsbx_cache';

        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
                throw ConfigurationException::invalidCacheDirectory($this->cacheDir);
            }
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            return $default;
        }

        $data = unserialize($content);
        if ($data === false) {
            return $default;
        }

        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $filename = $this->getFilename($key);

        $data = [
            'value' => $value,
            'expires_at' => $ttl !== null ? time() + $ttl : null,
        ];

        return file_put_contents($filename, serialize($data), LOCK_EX) !== false;
    }

    public function delete(string $key): bool
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    private function getFilename(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*.cache');
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
