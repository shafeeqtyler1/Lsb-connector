<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Response;

class Transaction
{
    public function __construct(
        public readonly string $id,
        public readonly string $accountId,
        public readonly ?int $transactionNumber = null,
        public readonly ?string $transactionType = null,
        public readonly ?string $typeDescription = null,
        public readonly ?string $statusCode = null,
        public readonly ?string $statusDescription = null,
        public readonly float $amount = 0.0,
        public readonly ?string $creditOrDebit = null,
        public readonly float $runningBalance = 0.0,
        public readonly ?string $originalPostDate = null,
        public readonly ?string $originalEffectiveDate = null,
        public readonly ?string $currentEffectiveDate = null,
        public readonly float $cashBackAmount = 0.0,
        public readonly ?string $description = null,
        public readonly ?string $externalDescription = null,
        public readonly ?string $batchId = null,
        public readonly ?string $allotmentNumber = null,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            accountId: $data['account_id'] ?? '',
            transactionNumber: $data['transaction_number'] ?? null,
            transactionType: $data['transaction_type'] ?? null,
            typeDescription: $data['type_description'] ?? null,
            statusCode: $data['status_code'] ?? null,
            statusDescription: $data['status_description'] ?? null,
            amount: (float) ($data['amount'] ?? 0),
            creditOrDebit: $data['credit_or_debit'] ?? null,
            runningBalance: (float) ($data['running_balance'] ?? 0),
            originalPostDate: $data['original_post_date'] ?? null,
            originalEffectiveDate: $data['original_effective_date'] ?? null,
            currentEffectiveDate: $data['current_effective_date'] ?? null,
            cashBackAmount: (float) ($data['cash_back_amount'] ?? 0),
            description: $data['description'] ?? null,
            externalDescription: $data['external_description'] ?? null,
            batchId: $data['batch_id'] ?? null,
            allotmentNumber: $data['allotment_number'] ?? null,
            rawData: $data,
        );
    }

    public function isCompleted(): bool
    {
        return $this->statusCode === 'C';
    }

    public function isPending(): bool
    {
        return $this->statusCode === 'P';
    }

    public function isCredit(): bool
    {
        return $this->creditOrDebit === 'CREDIT';
    }

    public function isDebit(): bool
    {
        return $this->creditOrDebit === 'DEBIT';
    }
}
