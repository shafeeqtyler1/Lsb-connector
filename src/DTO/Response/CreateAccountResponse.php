<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Response;

class CreateAccountResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $customerId,
        public readonly string $status,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            customerId: $data['customer_id'] ?? '',
            status: $data['status'] ?? '',
            rawData: $data,
        );
    }

    public function isActive(): bool
    {
        return strtoupper($this->status) === 'ACTIVE';
    }
}
