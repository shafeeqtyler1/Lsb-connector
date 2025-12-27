<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Tests\Unit\Resources;

use Shafeeq\LsbConnector\Tests\TestCase;
use Shafeeq\LsbConnector\Tests\Helpers\MockHttpClient;
use Shafeeq\LsbConnector\Tests\Helpers\TestDataFactory;
use Shafeeq\LsbConnector\Resources\Customers;
use Shafeeq\LsbConnector\DTO\Request\Customer\SearchCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\UpdateCustomerRequest;
use Shafeeq\LsbConnector\DTO\Response\CreateCustomerResponse;
use Shafeeq\LsbConnector\DTO\Response\Customer;

class CustomersTest extends TestCase
{
    private MockHttpClient $httpClient;
    private Customers $customers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new MockHttpClient();
        $this->customers = new Customers($this->httpClient);
    }

    public function test_create_person_customer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerResponse());

        $request = TestDataFactory::createPersonCustomerRequest();
        $response = $this->customers->createPerson($request);

        $this->assertInstanceOf(CreateCustomerResponse::class, $response);
        $this->assertEquals('109392', $response->customerId);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('customers', $lastRequest['endpoint']);
        $this->assertArrayHasKey('Idempotency-Key', $lastRequest['headers']);
    }

    public function test_create_person_customer_with_idempotency_key(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerResponse());

        $request = TestDataFactory::createPersonCustomerRequest();
        $idempotencyKey = 'custom-idempotency-key-123';
        $response = $this->customers->createPerson($request, $idempotencyKey);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals($idempotencyKey, $lastRequest['headers']['Idempotency-Key']);
    }

    public function test_create_organization_customer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerResponse([
            'customer_id' => '109393',
        ]));

        $request = TestDataFactory::createOrganizationCustomerRequest();
        $response = $this->customers->createOrganization($request);

        $this->assertInstanceOf(CreateCustomerResponse::class, $response);
        $this->assertEquals('109393', $response->customerId);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('ORGANIZATION', $lastRequest['data']['type']);
    }

    public function test_search_customer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse());

        $request = new SearchCustomerRequest(firstName: 'John', lastName: 'Doe');
        $customer = $this->customers->search($request);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('109392', $customer->customerId);
        $this->assertEquals('PERSON', $customer->type);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('customers/search', $lastRequest['endpoint']);
    }

    public function test_search_customer_returns_null_when_not_found(): void
    {
        $this->httpClient->addMockResponse(200, []);

        $request = SearchCustomerRequest::byName('Unknown', 'Person');
        $customer = $this->customers->search($request);

        $this->assertNull($customer);
    }

    public function test_find_by_name(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse());

        $customer = $this->customers->findByName('John', 'Doe');

        $this->assertInstanceOf(Customer::class, $customer);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('John', $lastRequest['data']['first_name']);
        $this->assertEquals('Doe', $lastRequest['data']['last_name']);
    }

    public function test_find_by_tax_id(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse());

        $customer = $this->customers->findByTaxId('123456789');

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('123456789', $lastRequest['data']['tax_id']);
    }

    public function test_find_by_customer_id(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse());

        $customer = $this->customers->findById('109392');

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('109392', $lastRequest['data']['customer_id']);
    }

    public function test_find_organization_by_name(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse([
            'type' => 'ORGANIZATION',
        ]));

        $customer = $this->customers->findOrganizationByName('Test Corp');

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('Test Corp', $lastRequest['data']['organization_name']);
        $this->assertTrue($lastRequest['data']['is_organization']);
    }

    public function test_update_customer(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $request = UpdateCustomerRequest::forPerson(TestDataFactory::personDetails());
        $result = $this->customers->update('109392', $request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('PATCH', $lastRequest['method']);
        $this->assertEquals('customers/109392', $lastRequest['endpoint']);
        $this->assertEquals('PERSON', $lastRequest['data']['type']);
    }

    public function test_customer_is_person(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse([
            'type' => 'PERSON',
        ]));

        $customer = $this->customers->findById('109392');

        $this->assertTrue($customer->isPerson());
        $this->assertFalse($customer->isOrganization());
    }

    public function test_customer_is_organization(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::customerSearchResponse([
            'type' => 'ORGANIZATION',
        ]));

        $customer = $this->customers->findById('109392');

        $this->assertFalse($customer->isPerson());
        $this->assertTrue($customer->isOrganization());
    }
}
