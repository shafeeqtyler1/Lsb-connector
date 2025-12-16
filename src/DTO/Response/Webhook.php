<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Response;

class Webhook
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $url = null,
        public readonly array $eventScopes = [],
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            url: $data['url'] ?? null,
            eventScopes: $data['event_scopes'] ?? [],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            rawData: $data,
        );
    }
}
