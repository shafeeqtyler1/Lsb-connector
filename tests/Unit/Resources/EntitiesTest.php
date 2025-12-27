<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests\Unit\Resources;

use ShafeeqKt\LsbConnector\Tests\TestCase;
use ShafeeqKt\LsbConnector\Tests\Helpers\MockHttpClient;
use ShafeeqKt\LsbConnector\Tests\Helpers\TestDataFactory;
use ShafeeqKt\LsbConnector\Resources\Entities;
use ShafeeqKt\LsbConnector\DTO\Request\Entity\CreateEntityRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Entity\UpdateEntityRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Entity\SearchEntityRequest;
use ShafeeqKt\LsbConnector\DTO\Response\Entity;

class EntitiesTest extends TestCase
{
    private MockHttpClient $httpClient;
    private Entities $entities;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new MockHttpClient();
        $this->entities = new Entities($this->httpClient);
    }

    public function test_create_entity(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse());

        $request = TestDataFactory::createEntityRequest();
        $entity = $this->entities->create($request);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('entity_id_123', $entity->id);
        $this->assertEquals('123456789', $entity->accountNumber);
        $this->assertEquals('021000021', $entity->routingNumber);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('entities', $lastRequest['endpoint']);
        $this->assertArrayHasKey('Idempotency-Key', $lastRequest['headers']);
    }

    public function test_create_entity_with_custom_idempotency_key(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse());

        $request = TestDataFactory::createEntityRequest();
        $idempotencyKey = 'custom-key-456';
        $entity = $this->entities->create($request, $idempotencyKey);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals($idempotencyKey, $lastRequest['headers']['Idempotency-Key']);
    }

    public function test_create_entity_generates_custom_string(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse());

        $request = new CreateEntityRequest(
            accountNumber: '123456789',
            routingNumber: '021000021',
            accountHolderName: 'Test Holder'
        );
        $this->entities->create($request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertArrayHasKey('custom_string', $lastRequest['data']);
        $this->assertNotEmpty($lastRequest['data']['custom_string']);
    }

    public function test_create_entity_sanitizes_account_holder_name(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse());

        $request = new CreateEntityRequest(
            accountNumber: '123456789',
            routingNumber: '021000021',
            accountHolderName: 'Test@Holder#With$Special!Chars'
        );
        $this->entities->create($request);

        $lastRequest = $this->httpClient->getLastRequest();
        // Max 22 chars, alphanumeric only
        $this->assertEquals('TestHolderWithSpecialC', $lastRequest['data']['account_holder_name']);
    }

    public function test_list_entities(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::entityResponse(),
            TestDataFactory::entityResponse(['id' => 'entity_2']),
        ]);

        $entities = $this->entities->list();

        $this->assertCount(2, $entities);
        $this->assertInstanceOf(Entity::class, $entities[0]);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('GET', $lastRequest['method']);
        $this->assertEquals('entities', $lastRequest['endpoint']);
    }

    public function test_list_entities_with_pagination(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::entityResponse(),
        ]);

        $entities = $this->entities->list(pageNumber: 2, recordsPerPage: 20);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals(2, $lastRequest['query']['page_number']);
        $this->assertEquals(20, $lastRequest['query']['records_per_page']);
    }

    public function test_search_entities(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::entityResponse(),
        ]);

        $request = SearchEntityRequest::byAccountAndRouting('123456789', '021000021');
        $entities = $this->entities->search($request);

        $this->assertCount(1, $entities);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('entities/search', $lastRequest['endpoint']);
        $this->assertEquals('123456789', $lastRequest['data']['account_number']);
        $this->assertEquals('021000021', $lastRequest['data']['routing_number']);
    }

    public function test_find_by_account_and_routing(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::entityResponse(),
        ]);

        $entity = $this->entities->findByAccountAndRouting('123456789', '021000021');

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('entity_id_123', $entity->id);
    }

    public function test_find_by_account_and_routing_returns_null_when_not_found(): void
    {
        $this->httpClient->addMockResponse(200, []);

        $entity = $this->entities->findByAccountAndRouting('999999999', '000000000');

        $this->assertNull($entity);
    }

    public function test_update_entity(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse([
            'description' => 'Updated Description',
        ]));

        $request = new UpdateEntityRequest(description: 'Updated Description');
        $entity = $this->entities->update('entity_id_123', $request);

        $this->assertInstanceOf(Entity::class, $entity);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('PATCH', $lastRequest['method']);
        $this->assertEquals('entities/entity_id_123', $lastRequest['endpoint']);
        $this->assertEquals('Updated Description', $lastRequest['data']['description']);
    }

    public function test_delete_entity(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $result = $this->entities->delete('entity_id_123');

        $this->assertTrue($result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest['method']);
        $this->assertEquals('entities/entity_id_123', $lastRequest['endpoint']);
    }

    public function test_entity_is_checking(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse([
            'account_type' => 'Checking',
        ]));

        $request = SearchEntityRequest::byAccountAndRouting('123', '456');
        $entities = $this->entities->search($request);

        $this->assertTrue($entities[0]->isChecking());
        $this->assertFalse($entities[0]->isSavings());
    }

    public function test_entity_is_savings(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::entityResponse([
            'account_type' => 'Savings',
        ]));

        $request = SearchEntityRequest::byAccountAndRouting('123', '456');
        $entities = $this->entities->search($request);

        $this->assertFalse($entities[0]->isChecking());
        $this->assertTrue($entities[0]->isSavings());
    }
}
