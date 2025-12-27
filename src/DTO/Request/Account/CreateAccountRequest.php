<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Account;

class CreateAccountRequest
{
    public const TYPE_PERSON = 'PERSON';
    public const TYPE_ORGANIZATION = 'ORGANIZATION';

    public const PRODUCT_TYPE_CHECKING = 'CHECKING';
    public const PRODUCT_TYPE_SAVINGS = 'SAVINGS';

    public const OWNERSHIP_SINGLE = 'SINGLE';
    public const OWNERSHIP_JOINT = 'JOINT';

    public function __construct(
        public readonly string $type,
        public readonly string $customerId,
        public readonly string $productType = self::PRODUCT_TYPE_CHECKING,
        public readonly string $productCode = 'FREE',
        public readonly string $ownershipType = self::OWNERSHIP_SINGLE,
        public readonly ?string $description = null,
    ) {}

    public function toArray(): array
    {
        $accountDetails = [
            'product_type' => $this->productType,
            'product_code' => $this->productCode,
            'customer_id' => $this->customerId,
            'ownership_type' => $this->ownershipType,
        ];

        if ($this->description !== null) {
            $accountDetails['description'] = $this->description;
        }

        return [
            'type' => $this->type,
            'account_details' => $accountDetails,
        ];
    }

    public static function checking(string $customerId, string $type = self::TYPE_PERSON): self
    {
        return new self(
            type: $type,
            customerId: $customerId,
            productType: self::PRODUCT_TYPE_CHECKING
        );
    }

    public static function savings(string $customerId, string $type = self::TYPE_PERSON): self
    {
        return new self(
            type: $type,
            customerId: $customerId,
            productType: self::PRODUCT_TYPE_SAVINGS
        );
    }
}
