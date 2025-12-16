<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Customer;

class SearchCustomerRequest
{
    public function __construct(
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $taxId = null,
        public readonly ?string $customerId = null,
        public readonly ?string $organizationName = null,
        public readonly bool $isOrganization = false,
    ) {}

    public function toArray(): array
    {
        $data = ['is_organization' => $this->isOrganization];

        if ($this->firstName !== null) {
            $data['first_name'] = $this->firstName;
        }

        if ($this->lastName !== null) {
            $data['last_name'] = $this->lastName;
        }

        if ($this->taxId !== null) {
            $data['tax_id'] = $this->taxId;
        }

        if ($this->customerId !== null) {
            $data['customer_id'] = $this->customerId;
        }

        if ($this->organizationName !== null) {
            $data['organization_name'] = $this->organizationName;
        }

        return $data;
    }

    public static function byName(string $firstName, string $lastName): self
    {
        return new self(firstName: $firstName, lastName: $lastName);
    }

    public static function byTaxId(string $taxId): self
    {
        return new self(taxId: $taxId);
    }

    public static function byCustomerId(string $customerId): self
    {
        return new self(customerId: $customerId);
    }

    public static function byOrganizationName(string $organizationName): self
    {
        return new self(organizationName: $organizationName, isOrganization: true);
    }
}
