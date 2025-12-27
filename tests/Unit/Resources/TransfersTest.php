<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests\Unit\Resources;

use ShafeeqKt\LsbConnector\Tests\TestCase;
use ShafeeqKt\LsbConnector\Tests\Helpers\MockHttpClient;
use ShafeeqKt\LsbConnector\Tests\Helpers\TestDataFactory;
use ShafeeqKt\LsbConnector\Resources\Transfers;
use ShafeeqKt\LsbConnector\DTO\Request\Transfer\CreateAchTransferRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Transfer\CancelAchTransferRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Transfer\CreateBookTransferRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Transfer\CreateWireTransferRequest;
use ShafeeqKt\LsbConnector\DTO\Common\Address;
use ShafeeqKt\LsbConnector\DTO\Response\Transfer;

class TransfersTest extends TestCase
{
    private MockHttpClient $httpClient;
    private Transfers $transfers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new MockHttpClient();
        $this->transfers = new Transfers($this->httpClient);
    }

    public function test_create_ach_transfer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse());

        $request = TestDataFactory::createAchTransferRequest();
        $transfer = $this->transfers->createAch($request);

        $this->assertInstanceOf(Transfer::class, $transfer);
        $this->assertEquals('transfer_id_123', $transfer->id);
        $this->assertEquals('PENDING', $transfer->status);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('transfers/ach', $lastRequest['endpoint']);
        $this->assertArrayHasKey('Idempotency-Key', $lastRequest['headers']);
    }

    public function test_create_ach_debit_transfer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'DEBIT',
        ]));

        $transfer = $this->transfers->debit(
            accountId: 'acc_123',
            entityId: 'ent_456',
            amount: 100.00,
            description: 'Test Debit',
            sameDayAch: true
        );

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('DEBIT', $lastRequest['data']['type']);
        $this->assertEquals(100.00, $lastRequest['data']['amount']);
        $this->assertTrue($lastRequest['data']['same_day_ach']);
    }

    public function test_create_ach_credit_transfer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'CREDIT',
        ]));

        $transfer = $this->transfers->credit(
            accountId: 'acc_123',
            entityId: 'ent_456',
            amount: 200.00,
            description: 'Test Credit'
        );

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('CREDIT', $lastRequest['data']['type']);
        $this->assertEquals(200.00, $lastRequest['data']['amount']);
    }

    public function test_ach_transfer_sanitizes_description(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse());

        $request = new CreateAchTransferRequest(
            accountId: 'acc_123',
            entityId: 'ent_456',
            type: CreateAchTransferRequest::TYPE_DEBIT,
            amount: 100.00,
            description: 'Test@Description#With$Special!Chars'
        );
        $this->transfers->createAch($request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('TestDescriptionWithSpecialChars', $lastRequest['data']['description']);
    }

    public function test_get_pending_ach_transfers(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::transferResponse(),
            TestDataFactory::transferResponse(['id' => 'transfer_2']),
        ]);

        $transfers = $this->transfers->getPendingAch('acc_123');

        $this->assertCount(2, $transfers);
        $this->assertInstanceOf(Transfer::class, $transfers[0]);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('GET', $lastRequest['method']);
        $this->assertEquals('transfers/ach/acc_123', $lastRequest['endpoint']);
    }

    public function test_cancel_ach_transfer(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $request = new CancelAchTransferRequest(
            id: 'transfer_123',
            accountId: 'acc_123'
        );
        $result = $this->transfers->cancelAch($request);

        $this->assertTrue($result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest['method']);
        $this->assertEquals('transfers/ach', $lastRequest['endpoint']);
        $this->assertEquals('transfer_123', $lastRequest['data']['id']);
        $this->assertEquals('acc_123', $lastRequest['data']['account_id']);
    }

    public function test_cancel_ach_by_id(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $result = $this->transfers->cancelAchById('transfer_123', 'acc_123');

        $this->assertTrue($result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('transfer_123', $lastRequest['data']['id']);
    }

    public function test_create_book_transfer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'BOOK',
        ]));

        $request = TestDataFactory::createBookTransferRequest();
        $transfer = $this->transfers->createBook($request);

        $this->assertInstanceOf(Transfer::class, $transfer);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('transfers/book', $lastRequest['endpoint']);
        $this->assertEquals('from_account_id', $lastRequest['data']['from_account_id']);
        $this->assertEquals('to_account_id', $lastRequest['data']['to_account_id']);
    }

    public function test_internal_transfer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse());

        $transfer = $this->transfers->internalTransfer(
            fromAccountId: 'from_acc',
            toAccountId: 'to_acc',
            amount: 500.00,
            description: 'Internal Transfer'
        );

        $this->assertInstanceOf(Transfer::class, $transfer);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('from_acc', $lastRequest['data']['from_account_id']);
        $this->assertEquals('to_acc', $lastRequest['data']['to_account_id']);
        $this->assertEquals(500.00, $lastRequest['data']['amount']);
    }

    public function test_create_wire_transfer(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'WIRE',
        ]));

        // Test wire to individual using factory method
        $request = CreateWireTransferRequest::toIndividual(
            accountId: 'acc_123',
            entityId: 'ent_456',
            amount: 10000.00,
            effectiveDate: '2024-12-15',
            firstName: 'John',
            lastName: 'Doe',
            recipientAddress: new Address(
                street: '123 Main St',
                city: 'New York',
                state: 'NY',
                postalCode: '10001'
            ),
            description: 'Wire Payment',
            externalDescription: 'Wire Pmt'
        );
        $transfer = $this->transfers->createWire($request);

        $this->assertInstanceOf(Transfer::class, $transfer);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('transfers/wire', $lastRequest['endpoint']);
        $this->assertEquals('DOMESTIC', $lastRequest['data']['type']);
        $this->assertArrayHasKey('wire_details', $lastRequest['data']);
        $this->assertEquals(10000.00, $lastRequest['data']['wire_details']['amount']);
        $this->assertEquals('acc_123', $lastRequest['data']['wire_details']['account_id']);
        $this->assertEquals('ent_456', $lastRequest['data']['wire_details']['entity_id']);
        $this->assertEquals('Wire Payment', $lastRequest['data']['wire_details']['description']);
        $this->assertEquals('Wire Pmt', $lastRequest['data']['wire_details']['external_description']);
        $this->assertFalse($lastRequest['data']['wire_details']['fbo_account_override']);
        $this->assertArrayHasKey('recipient_details', $lastRequest['data']['wire_details']);
        // Individual: first_name and last_name (no 'name')
        $this->assertArrayNotHasKey('name', $lastRequest['data']['wire_details']['recipient_details']);
        $this->assertEquals('John', $lastRequest['data']['wire_details']['recipient_details']['first_name']);
        $this->assertEquals('Doe', $lastRequest['data']['wire_details']['recipient_details']['last_name']);
        $this->assertArrayHasKey('address', $lastRequest['data']['wire_details']['recipient_details']);
        $this->assertEquals('123 Main St', $lastRequest['data']['wire_details']['recipient_details']['address']['street_line_1']);
    }

    public function test_create_wire_transfer_to_business(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'WIRE',
        ]));

        $request = CreateWireTransferRequest::toBusiness(
            accountId: 'acc_123',
            entityId: 'ent_456',
            amount: 50000.00,
            effectiveDate: '2024-12-15',
            businessName: 'Acme Corporation',
            recipientAddress: new Address(
                street: '456 Business Ave',
                city: 'Los Angeles',
                state: 'CA',
                postalCode: '90001'
            ),
            description: 'Business Wire Payment'
        );
        $transfer = $this->transfers->createWire($request);

        $this->assertInstanceOf(Transfer::class, $transfer);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('DOMESTIC', $lastRequest['data']['type']);
        // Business: name only (no first_name/last_name)
        $this->assertEquals('Acme Corporation', $lastRequest['data']['wire_details']['recipient_details']['name']);
        $this->assertArrayNotHasKey('first_name', $lastRequest['data']['wire_details']['recipient_details']);
        $this->assertArrayNotHasKey('last_name', $lastRequest['data']['wire_details']['recipient_details']);
    }

    public function test_get_pending_wire_transfers(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::transferResponse(['type' => 'WIRE']),
        ]);

        $transfers = $this->transfers->getPendingWire('acc_123');

        $this->assertCount(1, $transfers);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('GET', $lastRequest['method']);
        $this->assertEquals('transfers/wire/acc_123', $lastRequest['endpoint']);
    }

    public function test_transfer_is_pending(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'status' => 'PENDING',
        ]));

        $request = TestDataFactory::createAchTransferRequest();
        $transfer = $this->transfers->createAch($request);

        $this->assertTrue($transfer->isPending());
        $this->assertFalse($transfer->isCompleted());
    }

    public function test_transfer_is_completed(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'status' => 'COMPLETED',
        ]));

        $request = TestDataFactory::createAchTransferRequest();
        $transfer = $this->transfers->createAch($request);

        $this->assertFalse($transfer->isPending());
        $this->assertTrue($transfer->isCompleted());
    }

    public function test_transfer_is_debit(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'DEBIT',
        ]));

        $transfer = $this->transfers->debit('acc', 'ent', 100, 'test');

        $this->assertTrue($transfer->isDebit());
        $this->assertFalse($transfer->isCredit());
    }

    public function test_transfer_is_credit(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transferResponse([
            'type' => 'CREDIT',
        ]));

        $transfer = $this->transfers->credit('acc', 'ent', 100, 'test');

        $this->assertFalse($transfer->isDebit());
        $this->assertTrue($transfer->isCredit());
    }
}
