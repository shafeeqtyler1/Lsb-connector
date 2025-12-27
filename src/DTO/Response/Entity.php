<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Response;

class Entity
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $customerId = null,
        public readonly ?string $accountNumber = null,
        public readonly ?string $routingNumber = null,
        public readonly ?string $accountType = null,
        public readonly ?string $accountHolderName = null,
        public readonly bool $isOrganization = false,
        public readonly ?string $description = null,
        public readonly ?string $customString = null,
        public readonly ?string $financialInstitutionName = null,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            customerId: $data['customer_id'] ?? null,
            accountNumber: $data['account_number'] ?? null,
            routingNumber: $data['routing_number'] ?? null,
            accountType: $data['account_type'] ?? null,
            accountHolderName: $data['account_holder_name'] ?? null,
            isOrganization: $data['is_organization'] ?? false,
            description: $data['description'] ?? null,
            customString: $data['custom_string'] ?? null,
            financialInstitutionName: $data['financial_institution_name'] ?? null,
            rawData: $data,
        );
    }

    public function isChecking(): bool
    {
        return strtolower($this->accountType ?? '') === 'checking';
    }

    public function isSavings(): bool
    {
        return strtolower($this->accountType ?? '') === 'savings';
    }
}
