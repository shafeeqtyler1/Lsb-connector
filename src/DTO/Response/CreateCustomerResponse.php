<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Response;

class CreateCustomerResponse
{
    public function __construct(
        public readonly string $customerId,
        public readonly array $accounts = [],
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerId: $data['customer_id'] ?? '',
            accounts: $data['accounts'] ?? [],
            rawData: $data,
        );
    }
}
