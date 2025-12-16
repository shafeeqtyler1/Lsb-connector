<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Response;

class AccountLimits
{
    public function __construct(
        public readonly string $id,
        public readonly string $accountId,
        public readonly ?string $fintechId = null,
        public readonly float $achDailyLimit = 0.0,
        public readonly float $achPerTransactionLimit = 0.0,
        public readonly float $usedAchDailyLimit = 0.0,
        public readonly float $availableAchDailyLimit = 0.0,
        public readonly ?string $createdDateTime = null,
        public readonly ?string $updatedDateTime = null,
        public readonly bool $isDeleted = false,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            accountId: $data['account_id'] ?? '',
            fintechId: $data['fintech_id'] ?? null,
            achDailyLimit: (float) ($data['ach_daily_limit'] ?? 0),
            achPerTransactionLimit: (float) ($data['ach_per_transaction_limit'] ?? 0),
            usedAchDailyLimit: (float) ($data['used_ach_daily_limit'] ?? 0),
            availableAchDailyLimit: (float) ($data['available_ach_daily_limit'] ?? 0),
            createdDateTime: $data['created_date_time'] ?? null,
            updatedDateTime: $data['updated_date_time'] ?? null,
            isDeleted: $data['is_deleted'] ?? false,
            rawData: $data,
        );
    }

    public function getRemainingDailyLimit(): float
    {
        return $this->availableAchDailyLimit;
    }
}
