<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Common;

class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $state,
        public readonly string $postalCode,
        public readonly string $country = 'USA',
        public readonly ?string $streetLine2 = null,
        public readonly ?string $region = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'street' => $this->street,
            'street_line_2' => $this->streetLine2,
            'city' => $this->city,
            'state' => $this->state,
            'region' => $this->region,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? '',
            city: $data['city'] ?? '',
            state: $data['state'] ?? '',
            postalCode: $data['postal_code'] ?? '',
            country: $data['country'] ?? 'USA',
            streetLine2: $data['street_line_2'] ?? null,
            region: $data['region'] ?? null,
        );
    }
}
