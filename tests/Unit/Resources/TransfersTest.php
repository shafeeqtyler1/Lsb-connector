<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Tests\Unit\Resources;

use Shafeeq\LsbConnector\Tests\TestCase;
use Shafeeq\LsbConnector\Tests\Helpers\MockHttpClient;
use Shafeeq\LsbConnector\Tests\Helpers\TestDataFactory;
use Shafeeq\LsbConnector\Resources\Transfers;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CancelAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateBookTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateWireTransferRequest;
use Shafeeq\LsbConnector\DTO\Common\Address;
use Shafeeq\LsbConnector\DTO\Response\Transfer;

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

        $request = new CreateWireTransferRequest(
            accountId: 'acc_123',
            entityId: 'ent_456',
            amount: 10000.00,
            description: 'Wire Payment',
            effectiveDate: '2024-12-15',
            recipientName: 'John Doe',
            recipientAddress: new Address(
                street: '123 Main St',
                city: 'New York',
                state: 'NY',
                postalCode: '10001'
            )
        );
        $transfer = $this->transfers->createWire($request);

        $this->assertInstanceOf(Transfer::class, $transfer);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('transfers/wire', $lastRequest['endpoint']);
        $this->assertEquals(10000.00, $lastRequest['data']['amount']);
        $this->assertEquals('John Doe', $lastRequest['data']['recipient_name']);
        $this->assertArrayHasKey('recipient_address', $lastRequest['data']);
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
