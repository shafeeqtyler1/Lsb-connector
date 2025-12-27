<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Common;

class Identification
{
    public const TYPE_DRIVERS_LICENSE = 'DRIVERS_LICENSE';
    public const TYPE_PASSPORT = 'PASSPORT';
    public const TYPE_STATE_ID = 'STATE_ID';

    public function __construct(
        public readonly string $type,
        public readonly string $number,
        public readonly string $issueDate,
        public readonly string $expireDate,
        public readonly string $countryCode = 'USA',
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'number' => $this->number,
            'issue_date' => $this->issueDate,
            'expire_date' => $this->expireDate,
            'country_code' => $this->countryCode,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? '',
            number: $data['number'] ?? '',
            issueDate: $data['issue_date'] ?? '',
            expireDate: $data['expire_date'] ?? '',
            countryCode: $data['country_code'] ?? 'USA',
        );
    }
}
