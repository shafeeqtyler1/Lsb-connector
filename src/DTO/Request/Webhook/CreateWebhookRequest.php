<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Webhook;

class CreateWebhookRequest
{
    /**
     * @param string $url Webhook endpoint URL
     * @param string[] $eventScopes Event scopes to subscribe to
     * @param string $signingSecret Secret key for HMAC signature verification
     */
    public function __construct(
        public readonly string $url,
        public readonly array $eventScopes,
        public readonly string $signingSecret,
    ) {}

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'event_scopes' => $this->eventScopes,
            'signing_secret' => $this->signingSecret,
        ];
    }
}
