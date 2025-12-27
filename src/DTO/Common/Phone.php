<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Common;

class Phone
{
    public function __construct(
        public readonly string $number,
        public readonly string $countryCode = 'USA',
    ) {}

    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'number' => $this->number,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'] ?? '',
            countryCode: $data['country_code'] ?? 'USA',
        );
    }
}
