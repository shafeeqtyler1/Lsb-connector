<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Exceptions;

class ConfigurationException extends LsbxException
{
    public static function missingCredentials(): self
    {
        return new self('Client ID and Client Secret are required', 500);
    }

    public static function invalidCacheDirectory(string $path): self
    {
        return new self("Cache directory is not writable: {$path}", 500);
    }
}
