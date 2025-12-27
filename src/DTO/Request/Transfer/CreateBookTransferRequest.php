<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Transfer;

class CreateBookTransferRequest
{
    public function __construct(
        public readonly string $fromAccountId,
        public readonly string $toAccountId,
        public readonly float $amount,
        public readonly string $description,
    ) {}

    public function toArray(): array
    {
        return [
            'from_account_id' => $this->fromAccountId,
            'to_account_id' => $this->toAccountId,
            'amount' => $this->amount,
            'description' => $this->description,
        ];
    }
}
