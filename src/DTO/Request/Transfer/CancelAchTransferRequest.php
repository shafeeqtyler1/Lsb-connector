<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Transfer;

class CancelAchTransferRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $accountId,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
        ];
    }
}
