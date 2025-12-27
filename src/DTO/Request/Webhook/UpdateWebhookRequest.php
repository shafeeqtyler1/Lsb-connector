<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Webhook;

class UpdateWebhookRequest
{
    /**
     * @param string|null $url Webhook endpoint URL
     * @param string[]|null $eventScopes Event scopes to subscribe to
     * @param string|null $signingSecret Secret key for HMAC signature verification
     */
    public function __construct(
        public readonly ?string $url = null,
        public readonly ?array $eventScopes = null,
        public readonly ?string $signingSecret = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->eventScopes !== null) {
            $data['event_scopes'] = $this->eventScopes;
        }

        if ($this->signingSecret !== null) {
            $data['signing_secret'] = $this->signingSecret;
        }

        return $data;
    }
}
