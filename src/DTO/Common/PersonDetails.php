<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Common;

class PersonDetails
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $birthDate,
        public readonly Address $address,
        public readonly Phone $phone,
        public readonly Identification $identification,
        public readonly string $taxId,
        public readonly string $email,
        public readonly string $occupationCode,
        public readonly ?string $middleName = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'middle_name' => $this->middleName,
            'birth_date' => $this->birthDate,
            'address' => $this->address->toArray(),
            'phone' => $this->phone->toArray(),
            'identification' => $this->identification->toArray(),
            'tax_id' => $this->taxId,
            'email' => $this->email,
            'occupation_code' => $this->occupationCode,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'] ?? '',
            lastName: $data['last_name'] ?? '',
            birthDate: $data['birth_date'] ?? '',
            address: Address::fromArray($data['address'] ?? []),
            phone: Phone::fromArray($data['phone'] ?? []),
            identification: Identification::fromArray($data['identification'] ?? []),
            taxId: $data['tax_id'] ?? '',
            email: $data['email'] ?? '',
            occupationCode: $data['occupation_code'] ?? '',
            middleName: $data['middle_name'] ?? null,
        );
    }
}
