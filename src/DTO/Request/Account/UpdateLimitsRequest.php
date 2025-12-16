<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Account;

class UpdateLimitsRequest
{
    public function __construct(
        public readonly ?float $achDailyLimit = null,
        public readonly ?float $achPerTransactionLimit = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->achDailyLimit !== null) {
            $data['ach_daily_limit'] = $this->achDailyLimit;
        }

        if ($this->achPerTransactionLimit !== null) {
            $data['ach_per_transaction_limit'] = $this->achPerTransactionLimit;
        }

        return $data;
    }
}
