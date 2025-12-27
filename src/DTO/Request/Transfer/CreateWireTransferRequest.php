<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Transfer;

use ShafeeqKt\LsbConnector\DTO\Common\Address;

class CreateWireTransferRequest
{
    public const TYPE_DOMESTIC = 'DOMESTIC';

    /**
     * @param string $accountId Originating account ID
     * @param string $entityId Entity ID (beneficiary)
     * @param float $amount Transfer amount
     * @param string $effectiveDate Date (YYYY-MM-DD), must be today or future
     * @param Address $recipientAddress Recipient address (required)
     * @param string|null $recipientBusinessName Business name (for businesses only)
     * @param string|null $recipientFirstName First name (for individuals only)
     * @param string|null $recipientLastName Last name (for individuals only)
     * @param string|null $description Internal description (max 120 chars)
     * @param string|null $externalDescription External FI description (max 120 chars)
     * @param bool $fboAccountOverride Override FBO account logic
     * @param string $type Wire type (DOMESTIC only)
     */
    public function __construct(
        public readonly string $accountId,
        public readonly string $entityId,
        public readonly float $amount,
        public readonly string $effectiveDate,
        public readonly Address $recipientAddress,
        public readonly ?string $recipientBusinessName = null,
        public readonly ?string $recipientFirstName = null,
        public readonly ?string $recipientLastName = null,
        public readonly ?string $description = null,
        public readonly ?string $externalDescription = null,
        public readonly bool $fboAccountOverride = false,
        public readonly string $type = self::TYPE_DOMESTIC,
    ) {}

    /**
     * Create wire transfer to a business
     */
    public static function toBusiness(
        string $accountId,
        string $entityId,
        float $amount,
        string $effectiveDate,
        string $businessName,
        Address $recipientAddress,
        ?string $description = null,
        ?string $externalDescription = null,
        bool $fboAccountOverride = false
    ): self {
        return new self(
            accountId: $accountId,
            entityId: $entityId,
            amount: $amount,
            effectiveDate: $effectiveDate,
            recipientAddress: $recipientAddress,
            recipientBusinessName: $businessName,
            description: $description,
            externalDescription: $externalDescription,
            fboAccountOverride: $fboAccountOverride
        );
    }

    /**
     * Create wire transfer to an individual
     */
    public static function toIndividual(
        string $accountId,
        string $entityId,
        float $amount,
        string $effectiveDate,
        string $firstName,
        string $lastName,
        Address $recipientAddress,
        ?string $description = null,
        ?string $externalDescription = null,
        bool $fboAccountOverride = false
    ): self {
        return new self(
            accountId: $accountId,
            entityId: $entityId,
            amount: $amount,
            effectiveDate: $effectiveDate,
            recipientAddress: $recipientAddress,
            recipientFirstName: $firstName,
            recipientLastName: $lastName,
            description: $description,
            externalDescription: $externalDescription,
            fboAccountOverride: $fboAccountOverride
        );
    }

    public function toArray(): array
    {
        $recipientDetails = [];

        // For businesses, use 'name'
        if ($this->recipientBusinessName !== null) {
            $recipientDetails['name'] = $this->recipientBusinessName;
        }

        // For individuals, use 'first_name' and 'last_name'
        if ($this->recipientFirstName !== null) {
            $recipientDetails['first_name'] = $this->recipientFirstName;
        }

        if ($this->recipientLastName !== null) {
            $recipientDetails['last_name'] = $this->recipientLastName;
        }

        $recipientDetails['address'] = $this->recipientAddress->toWireArray();

        $wireDetails = [
            'account_id' => $this->accountId,
            'entity_id' => $this->entityId,
            'fbo_account_override' => $this->fboAccountOverride,
            'amount' => $this->amount,
            'effective_date' => $this->effectiveDate,
            'recipient_details' => $recipientDetails,
        ];

        if ($this->description !== null) {
            $wireDetails['description'] = $this->sanitizeDescription($this->description);
        }

        if ($this->externalDescription !== null) {
            $wireDetails['external_description'] = $this->sanitizeExternalDescription($this->externalDescription);
        }

        return [
            'type' => $this->type,
            'wire_details' => $wireDetails,
        ];
    }

    /**
     * Sanitize description (max 120 chars, no special chars)
     */
    private function sanitizeDescription(string $description): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9 ]/', '', $description);
        return substr($sanitized ?? $description, 0, 120);
    }

    /**
     * Sanitize external description (max 120 chars, no special chars)
     */
    private function sanitizeExternalDescription(string $description): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9 ]/', '', $description);
        return substr($sanitized ?? $description, 0, 120);
    }
}
