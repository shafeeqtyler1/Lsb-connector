<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Resources;

use ShafeeqKt\LsbConnector\DTO\Request\Webhook\CreateWebhookRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Webhook\UpdateWebhookRequest;
use ShafeeqKt\LsbConnector\DTO\Response\Webhook;
use ShafeeqKt\LsbConnector\DTO\Response\WebhookEvent;

class Webhooks extends AbstractResource
{
    /**
     * Available webhook event scopes
     */
    public const SCOPE_ACCOUNT_DEPOSIT = 'account.deposit';
    public const SCOPE_ACCOUNT_DEPOSIT_NOTICE = 'account.deposit.notice';
    public const SCOPE_ACCOUNT_DEPOSIT_STATUS = 'account.deposit.status';
    public const SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS = 'account.deposit.transactions';
    public const SCOPE_CUSTOMER = 'customer';

    /**
     * Create a new webhook
     *
     * @link https://docs.lsbxapi.com/#tag/Webhooks/operation/createWebhook
     */
    public function create(CreateWebhookRequest $request): Webhook
    {
        $response = $this->httpPost('webhook/create', $request->toArray());
        return Webhook::fromArray($response->getData() ?? []);
    }

    /**
     * Get all registered webhooks
     *
     * @link https://docs.lsbxapi.com/#tag/Webhooks/operation/getWebhooks
     * @return Webhook[]
     */
    public function list(): array
    {
        $response = $this->httpClient->get('webhook/list');
        $data = $response->getData() ?? [];

        if (isset($data[0])) {
            return array_map(
                fn(array $item) => Webhook::fromArray($item),
                $data
            );
        }

        if (isset($data['webhooks'])) {
            return array_map(
                fn(array $item) => Webhook::fromArray($item),
                $data['webhooks']
            );
        }

        return [];
    }

    /**
     * Get available webhook event scopes
     *
     * @link https://docs.lsbxapi.com/#tag/Webhooks/operation/getWebhookEvents
     * @return string[]
     */
    public function getEventScopes(): array
    {
        $response = $this->httpClient->get('webhook/list/events');
        $data = $response->getData() ?? [];

        return $data['event_scopes'] ?? [];
    }

    /**
     * Update a webhook
     *
     * @link https://docs.lsbxapi.com/#tag/Webhooks/operation/updateWebhook
     */
    public function update(string $webhookId, UpdateWebhookRequest $request): Webhook
    {
        $response = $this->httpPatch("webhook/{$webhookId}", $request->toArray());
        return Webhook::fromArray($response->getData() ?? []);
    }

    /**
     * Delete a webhook
     *
     * @link https://docs.lsbxapi.com/#tag/Webhooks/operation/deleteWebhook
     */
    public function delete(string $webhookId): bool
    {
        $response = $this->httpClient->delete("webhook/{$webhookId}");
        return $response->isSuccessful();
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from X-LSBX-Signature header
     * @param string $secret Your webhook signing secret
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse webhook payload into WebhookEvent object
     *
     * @param string $payload Raw JSON payload
     */
    public function parseEvent(string $payload): WebhookEvent
    {
        $data = json_decode($payload, true) ?? [];
        return WebhookEvent::fromArray($data);
    }

    /**
     * Parse multiple webhook events (batch delivery)
     *
     * @param string $payload Raw JSON payload containing array of events
     * @return WebhookEvent[]
     */
    public function parseEvents(string $payload): array
    {
        $data = json_decode($payload, true) ?? [];

        // Handle single event
        if (isset($data['event_id'])) {
            return [$this->parseEvent($payload)];
        }

        // Handle batch events
        return array_map(
            fn(array $event) => WebhookEvent::fromArray($event),
            $data
        );
    }
}
