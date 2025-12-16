<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Response;

class Transfer
{
    public function __construct(
        public readonly string $id,
        public readonly string $accountId,
        public readonly ?string $type = null,
        public readonly float $amount = 0.0,
        public readonly ?string $status = null,
        public readonly ?string $effectiveDate = null,
        public readonly ?string $description = null,
        public readonly ?string $entityId = null,
        public readonly ?string $recipientName = null,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? $data['transfer_id'] ?? '',
            accountId: $data['account_id'] ?? '',
            type: $data['type'] ?? null,
            amount: (float) ($data['amount'] ?? 0),
            status: $data['status'] ?? null,
            effectiveDate: $data['effective_date'] ?? null,
            description: $data['description'] ?? null,
            entityId: $data['entity_id'] ?? null,
            recipientName: $data['recipient_name'] ?? null,
            rawData: $data,
        );
    }

    public function isPending(): bool
    {
        return strtoupper($this->status ?? '') === 'PENDING';
    }

    public function isCompleted(): bool
    {
        return strtoupper($this->status ?? '') === 'COMPLETED';
    }

    public function isDebit(): bool
    {
        return strtoupper($this->type ?? '') === 'DEBIT';
    }

    public function isCredit(): bool
    {
        return strtoupper($this->type ?? '') === 'CREDIT';
    }
}
