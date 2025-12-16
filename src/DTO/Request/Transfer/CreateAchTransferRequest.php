<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Transfer;

class CreateAchTransferRequest
{
    public const TYPE_DEBIT = 'DEBIT';
    public const TYPE_CREDIT = 'CREDIT';

    public function __construct(
        public readonly string $accountId,
        public readonly string $entityId,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $description,
        public readonly bool $sameDayAch = false,
        public readonly ?string $externalDescription = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'account_id' => $this->accountId,
            'entity_id' => $this->entityId,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->sanitizeDescription($this->description),
            'same_day_ach' => $this->sameDayAch,
        ];

        if ($this->externalDescription !== null) {
            $data['external_description'] = $this->sanitizeExternalDescription($this->externalDescription);
        }

        return $data;
    }

    /**
     * Sanitize description (max 100 chars, no special chars)
     */
    private function sanitizeDescription(string $description): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9 ]/', '', $description);
        return substr($sanitized ?? $description, 0, 100);
    }

    /**
     * Sanitize external description (max 32 chars, no special chars)
     */
    private function sanitizeExternalDescription(string $description): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9 ]/', '', $description);
        return substr($sanitized ?? $description, 0, 32);
    }

    public static function debit(
        string $accountId,
        string $entityId,
        float $amount,
        string $description,
        bool $sameDayAch = false
    ): self {
        return new self(
            accountId: $accountId,
            entityId: $entityId,
            type: self::TYPE_DEBIT,
            amount: $amount,
            description: $description,
            sameDayAch: $sameDayAch
        );
    }

    public static function credit(
        string $accountId,
        string $entityId,
        float $amount,
        string $description,
        bool $sameDayAch = false
    ): self {
        return new self(
            accountId: $accountId,
            entityId: $entityId,
            type: self::TYPE_CREDIT,
            amount: $amount,
            description: $description,
            sameDayAch: $sameDayAch
        );
    }
}
