<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Account;

class CreateLimitsRequest
{
    public function __construct(
        public readonly float $achDailyLimit,
        public readonly float $achPerTransactionLimit,
    ) {}

    public function toArray(): array
    {
        return [
            'ach_daily_limit' => $this->achDailyLimit,
            'ach_per_transaction_limit' => $this->achPerTransactionLimit,
        ];
    }
}
