<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Common;

class OrganizationDetails
{
    public function __construct(
        public readonly string $name,
        public readonly string $formationDate,
        public readonly Address $address,
        public readonly Phone $phone,
        public readonly string $taxId,
        public readonly string $email,
        public readonly string $naicsCode,
        public readonly ?string $dbaName = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'dba_name' => $this->dbaName,
            'formation_date' => $this->formationDate,
            'address' => $this->address->toArray(),
            'phone' => $this->phone->toArray(),
            'tax_id' => $this->taxId,
            'email' => $this->email,
            'naics_code' => $this->naicsCode,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            formationDate: $data['formation_date'] ?? '',
            address: Address::fromArray($data['address'] ?? []),
            phone: Phone::fromArray($data['phone'] ?? []),
            taxId: $data['tax_id'] ?? '',
            email: $data['email'] ?? '',
            naicsCode: $data['naics_code'] ?? '',
            dbaName: $data['dba_name'] ?? null,
        );
    }
}
