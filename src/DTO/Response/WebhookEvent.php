<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Response;

class WebhookEvent
{
    public function __construct(
        public readonly ?string $eventId = null,
        public readonly ?string $eventDate = null,
        public readonly ?string $eventScope = null,
        public readonly ?string $eventCode = null,
        public readonly ?string $eventDescription = null,
        public readonly ?string $action = null,
        public readonly ?string $accountId = null,
        public readonly ?string $customerId = null,
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['event_id'] ?? null,
            eventDate: $data['event_date'] ?? null,
            eventScope: $data['event_scope'] ?? null,
            eventCode: $data['event_code'] ?? null,
            eventDescription: $data['event_description'] ?? null,
            action: $data['action'] ?? null,
            accountId: $data['account_id'] ?? null,
            customerId: $data['customer_id'] ?? null,
            rawData: $data,
        );
    }

    public function isCreated(): bool
    {
        return strtoupper($this->action ?? '') === 'CREATED';
    }

    public function isDeleted(): bool
    {
        return strtoupper($this->action ?? '') === 'DELETED';
    }

    public function isUpdated(): bool
    {
        return strtoupper($this->action ?? '') === 'UPDATED';
    }
}
