<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Entity;

class SearchEntityRequest
{
    public function __construct(
        public readonly ?string $accountNumber = null,
        public readonly ?string $routingNumber = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->accountNumber !== null) {
            $data['account_number'] = $this->accountNumber;
        }

        if ($this->routingNumber !== null) {
            $data['routing_number'] = $this->routingNumber;
        }

        return $data;
    }

    public static function byAccountAndRouting(string $accountNumber, string $routingNumber): self
    {
        return new self(accountNumber: $accountNumber, routingNumber: $routingNumber);
    }
}
