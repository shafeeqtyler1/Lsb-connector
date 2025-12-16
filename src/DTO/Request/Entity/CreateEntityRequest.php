<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Entity;

class CreateEntityRequest
{
    public const ACCOUNT_TYPE_CHECKING = 'Checking';
    public const ACCOUNT_TYPE_SAVINGS = 'Savings';

    public function __construct(
        public readonly string $accountNumber,
        public readonly string $routingNumber,
        public readonly string $accountHolderName,
        public readonly string $accountType = self::ACCOUNT_TYPE_CHECKING,
        public readonly ?string $description = null,
        public readonly ?string $customString = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'account_number' => $this->accountNumber,
            'routing_number' => $this->routingNumber,
            'account_type' => $this->accountType,
            'account_holder_name' => $this->sanitizeAccountHolderName($this->accountHolderName),
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        // Generate custom_string if not provided (hash of account-routing)
        $data['custom_string'] = $this->customString ?? hash(
            'sha256',
            $this->accountNumber . '-' . $this->routingNumber
        );

        return $data;
    }

    /**
     * Sanitize account holder name (max 22 chars, alphanumeric only)
     */
    private function sanitizeAccountHolderName(string $name): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
        return substr($sanitized ?? $name, 0, 22);
    }
}
