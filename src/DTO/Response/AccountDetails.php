<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Response;

class AccountDetails
{
    public function __construct(
        public readonly string $accountNumber,
        public readonly string $routingNumber,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: $data['account_number'] ?? '',
            routingNumber: $data['routing_number'] ?? '',
            rawData: $data,
        );
    }
}
