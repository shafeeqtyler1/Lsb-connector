<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests\Unit\Resources;

use ShafeeqKt\LsbConnector\Tests\TestCase;
use ShafeeqKt\LsbConnector\Tests\Helpers\MockHttpClient;
use ShafeeqKt\LsbConnector\Tests\Helpers\TestDataFactory;
use ShafeeqKt\LsbConnector\Resources\Webhooks;
use ShafeeqKt\LsbConnector\DTO\Request\Webhook\CreateWebhookRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Webhook\UpdateWebhookRequest;
use ShafeeqKt\LsbConnector\DTO\Response\Webhook;
use ShafeeqKt\LsbConnector\DTO\Response\WebhookEvent;

class WebhooksTest extends TestCase
{
    private MockHttpClient $httpClient;
    private Webhooks $webhooks;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new MockHttpClient();
        $this->webhooks = new Webhooks($this->httpClient);
    }

    public function test_create_webhook(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::webhookResponse());

        $request = new CreateWebhookRequest(
            url: 'https://example.com/webhook',
            eventScopes: [Webhooks::SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS, Webhooks::SCOPE_CUSTOMER],
            signingSecret: 'my-secret-key'
        );
        $webhook = $this->webhooks->create($request);

        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertEquals('webhook_id_123', $webhook->id);
        $this->assertEquals('https://example.com/webhook', $webhook->url);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('webhook/create', $lastRequest['endpoint']);
        $this->assertEquals('https://example.com/webhook', $lastRequest['data']['url']);
        $this->assertCount(2, $lastRequest['data']['event_scopes']);
    }

    public function test_list_webhooks(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::webhookResponse(),
            TestDataFactory::webhookResponse(['id' => 'webhook_2']),
        ]);

        $webhooks = $this->webhooks->list();

        $this->assertCount(2, $webhooks);
        $this->assertInstanceOf(Webhook::class, $webhooks[0]);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('GET', $lastRequest['method']);
        $this->assertEquals('webhook/list', $lastRequest['endpoint']);
    }

    public function test_get_event_scopes(): void
    {
        $this->httpClient->addMockResponse(200, [
            'event_scopes' => [
                'account.deposit',
                'account.deposit.notice',
                'account.deposit.status',
                'account.deposit.transactions',
                'customer',
            ],
        ]);

        $scopes = $this->webhooks->getEventScopes();

        $this->assertCount(5, $scopes);
        $this->assertContains('account.deposit.transactions', $scopes);
        $this->assertContains('customer', $scopes);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('webhook/list/events', $lastRequest['endpoint']);
    }

    public function test_update_webhook(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::webhookResponse([
            'url' => 'https://new-domain.com/webhook',
        ]));

        $request = new UpdateWebhookRequest(url: 'https://new-domain.com/webhook');
        $webhook = $this->webhooks->update('webhook_id_123', $request);

        $this->assertInstanceOf(Webhook::class, $webhook);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('PATCH', $lastRequest['method']);
        $this->assertEquals('webhook/webhook_id_123', $lastRequest['endpoint']);
        $this->assertEquals('https://new-domain.com/webhook', $lastRequest['data']['url']);
    }

    public function test_delete_webhook(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $result = $this->webhooks->delete('webhook_id_123');

        $this->assertTrue($result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest['method']);
        $this->assertEquals('webhook/webhook_id_123', $lastRequest['endpoint']);
    }

    public function test_verify_signature_valid(): void
    {
        $payload = json_encode(['event_id' => '123']);
        $secret = 'my-secret-key';
        $signature = hash_hmac('sha256', $payload, $secret);

        $isValid = $this->webhooks->verifySignature($payload, $signature, $secret);

        $this->assertTrue($isValid);
    }

    public function test_verify_signature_invalid(): void
    {
        $payload = json_encode(['event_id' => '123']);
        $secret = 'my-secret-key';
        $wrongSignature = 'invalid-signature';

        $isValid = $this->webhooks->verifySignature($payload, $wrongSignature, $secret);

        $this->assertFalse($isValid);
    }

    public function test_parse_event(): void
    {
        $payload = json_encode(TestDataFactory::webhookEventPayload());

        $event = $this->webhooks->parseEvent($payload);

        $this->assertInstanceOf(WebhookEvent::class, $event);
        $this->assertEquals('event_id_123', $event->eventId);
        $this->assertEquals('account.deposit.transactions', $event->eventScope);
        $this->assertEquals('DEPOSIT_ACCOUNT_TRANSACTION_TRANSFER', $event->eventCode);
        $this->assertEquals('CREATED', $event->action);
        $this->assertEquals('test_account_id_123', $event->accountId);
    }

    public function test_parse_events_single(): void
    {
        $payload = json_encode(TestDataFactory::webhookEventPayload());

        $events = $this->webhooks->parseEvents($payload);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(WebhookEvent::class, $events[0]);
    }

    public function test_parse_events_batch(): void
    {
        $payload = json_encode([
            TestDataFactory::webhookEventPayload(['event_id' => 'event_1']),
            TestDataFactory::webhookEventPayload(['event_id' => 'event_2']),
            TestDataFactory::webhookEventPayload(['event_id' => 'event_3']),
        ]);

        $events = $this->webhooks->parseEvents($payload);

        $this->assertCount(3, $events);
        $this->assertEquals('event_1', $events[0]->eventId);
        $this->assertEquals('event_2', $events[1]->eventId);
        $this->assertEquals('event_3', $events[2]->eventId);
    }

    public function test_webhook_event_is_created(): void
    {
        $payload = json_encode(TestDataFactory::webhookEventPayload(['action' => 'CREATED']));
        $event = $this->webhooks->parseEvent($payload);

        $this->assertTrue($event->isCreated());
        $this->assertFalse($event->isDeleted());
        $this->assertFalse($event->isUpdated());
    }

    public function test_webhook_event_is_deleted(): void
    {
        $payload = json_encode(TestDataFactory::webhookEventPayload(['action' => 'DELETED']));
        $event = $this->webhooks->parseEvent($payload);

        $this->assertFalse($event->isCreated());
        $this->assertTrue($event->isDeleted());
        $this->assertFalse($event->isUpdated());
    }

    public function test_webhook_event_is_updated(): void
    {
        $payload = json_encode(TestDataFactory::webhookEventPayload(['action' => 'UPDATED']));
        $event = $this->webhooks->parseEvent($payload);

        $this->assertFalse($event->isCreated());
        $this->assertFalse($event->isDeleted());
        $this->assertTrue($event->isUpdated());
    }

    public function test_webhook_constants(): void
    {
        $this->assertEquals('account.deposit', Webhooks::SCOPE_ACCOUNT_DEPOSIT);
        $this->assertEquals('account.deposit.notice', Webhooks::SCOPE_ACCOUNT_DEPOSIT_NOTICE);
        $this->assertEquals('account.deposit.status', Webhooks::SCOPE_ACCOUNT_DEPOSIT_STATUS);
        $this->assertEquals('account.deposit.transactions', Webhooks::SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS);
        $this->assertEquals('customer', Webhooks::SCOPE_CUSTOMER);
    }
}
