<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Transfer;

use Shafeeq\LsbConnector\DTO\Common\Address;

class CreateWireTransferRequest
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $entityId,
        public readonly float $amount,
        public readonly string $description,
        public readonly string $effectiveDate,
        public readonly string $recipientName,
        public readonly Address $recipientAddress,
    ) {}

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'entity_id' => $this->entityId,
            'amount' => $this->amount,
            'description' => $this->description,
            'effective_date' => $this->effectiveDate,
            'recipient_name' => $this->recipientName,
            'recipient_address' => $this->recipientAddress->toArray(),
        ];
    }
}
