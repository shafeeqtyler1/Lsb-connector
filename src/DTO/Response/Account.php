<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Response;

class Account
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $reportingForCustomerId = null,
        public readonly bool $isReportingForSigner = false,
        public readonly bool $isReportingForOwner = false,
        public readonly float $balance = 0.0,
        public readonly float $availableBalance = 0.0,
        public readonly ?string $productCategoryCode = null,
        public readonly ?string $productTypeCode = null,
        public readonly ?string $currentProductCode = null,
        public readonly ?string $productName = null,
        public readonly ?string $currentAccountStatusCode = null,
        public readonly bool $isValid = true,
        public readonly ?string $ownerCode = null,
        public readonly ?string $ownerDescription = null,
        public readonly ?string $contractDate = null,
        public readonly ?string $dateLastContact = null,
        public readonly ?string $description = null,
        public readonly bool $isTransactionAccount = true,
        public readonly bool $isFrozen = false,
        public readonly ?string $lastFrozenEffectiveDate = null,
        public readonly string $currencyCode = 'USD',
        public readonly ?string $currencyDescription = null,
        public readonly array $roles = [],
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            reportingForCustomerId: $data['reporting_for_customer_id'] ?? null,
            isReportingForSigner: $data['is_reporting_for_signer'] ?? false,
            isReportingForOwner: $data['is_reporting_for_owner'] ?? false,
            balance: (float) ($data['balance'] ?? 0),
            availableBalance: (float) ($data['available_balance'] ?? 0),
            productCategoryCode: $data['product_category_code'] ?? null,
            productTypeCode: $data['product_type_code'] ?? null,
            currentProductCode: $data['current_product_code'] ?? null,
            productName: $data['product_name'] ?? null,
            currentAccountStatusCode: $data['current_account_status_code'] ?? null,
            isValid: $data['is_valid'] ?? true,
            ownerCode: $data['owner_code'] ?? null,
            ownerDescription: $data['owner_description'] ?? null,
            contractDate: $data['contract_date'] ?? null,
            dateLastContact: $data['date_last_contact'] ?? null,
            description: $data['description'] ?? null,
            isTransactionAccount: $data['is_transaction_account'] ?? true,
            isFrozen: $data['is_frozen'] ?? false,
            lastFrozenEffectiveDate: $data['last_frozen_effective_date'] ?? null,
            currencyCode: $data['currency_code'] ?? 'USD',
            currencyDescription: $data['currency_description'] ?? null,
            roles: $data['roles'] ?? [],
            rawData: $data,
        );
    }

    public function isActive(): bool
    {
        return $this->currentAccountStatusCode === 'ACTIVE';
    }

    public function isChecking(): bool
    {
        return $this->productTypeCode === 'CHECKING';
    }

    public function isSavings(): bool
    {
        return $this->productTypeCode === 'SAVINGS';
    }
}
