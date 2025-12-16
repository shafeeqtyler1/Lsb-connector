<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Entity;

class UpdateEntityRequest
{
    public function __construct(
        public readonly ?string $accountNumber = null,
        public readonly ?string $routingNumber = null,
        public readonly ?string $accountHolderName = null,
        public readonly ?string $accountType = null,
        public readonly ?string $description = null,
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

        if ($this->accountHolderName !== null) {
            $data['account_holder_name'] = $this->accountHolderName;
        }

        if ($this->accountType !== null) {
            $data['account_type'] = $this->accountType;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        return $data;
    }
}
