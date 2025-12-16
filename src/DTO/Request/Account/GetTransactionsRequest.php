<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Account;

class GetTransactionsRequest
{
    public function __construct(
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?int $pageNumber = null,
        public readonly ?int $pageSize = null,
    ) {}

    public function toQueryParams(): array
    {
        $params = [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];

        if ($this->pageNumber !== null) {
            $params['page_number'] = $this->pageNumber;
        }

        if ($this->pageSize !== null) {
            $params['page_size'] = $this->pageSize;
        }

        return $params;
    }

    public static function forDateRange(string $startDate, string $endDate): self
    {
        return new self(startDate: $startDate, endDate: $endDate);
    }

    public static function lastDays(int $days): self
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        return new self(startDate: $startDate, endDate: $endDate);
    }
}
