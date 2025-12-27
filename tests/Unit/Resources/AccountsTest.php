<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\Tests\Unit\Resources;

use ShafeeqKt\LsbConnector\Tests\TestCase;
use ShafeeqKt\LsbConnector\Tests\Helpers\MockHttpClient;
use ShafeeqKt\LsbConnector\Tests\Helpers\TestDataFactory;
use ShafeeqKt\LsbConnector\Resources\Accounts;
use ShafeeqKt\LsbConnector\DTO\Request\Account\CreateAccountRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Account\UpdateAccountRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Account\CreateLimitsRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Account\UpdateLimitsRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Account\GetTransactionsRequest;
use ShafeeqKt\LsbConnector\DTO\Response\CreateAccountResponse;
use ShafeeqKt\LsbConnector\DTO\Response\Account;
use ShafeeqKt\LsbConnector\DTO\Response\AccountDetails;
use ShafeeqKt\LsbConnector\DTO\Response\AccountLimits;
use ShafeeqKt\LsbConnector\DTO\Response\Transaction;

class AccountsTest extends TestCase
{
    private MockHttpClient $httpClient;
    private Accounts $accounts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new MockHttpClient();
        $this->accounts = new Accounts($this->httpClient);
    }

    public function test_create_account(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountResponse());

        $request = TestDataFactory::createAccountRequest();
        $response = $this->accounts->create($request);

        $this->assertInstanceOf(CreateAccountResponse::class, $response);
        $this->assertEquals('test_account_id_123', $response->id);
        $this->assertEquals('109392', $response->customerId);
        $this->assertEquals('ACTIVE', $response->status);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('accounts', $lastRequest['endpoint']);
    }

    public function test_create_checking_account(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountResponse());

        $request = CreateAccountRequest::checking('109392');
        $response = $this->accounts->create($request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('CHECKING', $lastRequest['data']['account_details']['product_type']);
    }

    public function test_create_savings_account(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountResponse());

        $request = CreateAccountRequest::savings('109392');
        $response = $this->accounts->create($request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('SAVINGS', $lastRequest['data']['account_details']['product_type']);
    }

    public function test_get_account(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountDetailsResponse());

        $account = $this->accounts->get('test_account_id_123');

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('test_account_id_123', $account->id);
        $this->assertEquals(1000.00, $account->balance);
        $this->assertEquals(950.00, $account->availableBalance);
        $this->assertTrue($account->isActive());
        $this->assertTrue($account->isChecking());

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('GET', $lastRequest['method']);
        $this->assertEquals('accounts/test_account_id_123', $lastRequest['endpoint']);
    }

    public function test_get_account_details(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountBankingDetailsResponse());

        $details = $this->accounts->getDetails('test_account_id_123');

        $this->assertInstanceOf(AccountDetails::class, $details);
        $this->assertEquals('1234567890', $details->accountNumber);
        $this->assertEquals('073905527', $details->routingNumber);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('accounts/test_account_id_123/details', $lastRequest['endpoint']);
    }

    public function test_update_account(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $request = new UpdateAccountRequest(type: 'PERSON', description: 'Updated description');
        $result = $this->accounts->update('test_account_id_123', $request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('PATCH', $lastRequest['method']);
        $this->assertEquals('accounts/test_account_id_123', $lastRequest['endpoint']);
    }

    public function test_freeze_account(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $result = $this->accounts->freezeAccount('test_account_id_123');

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals('accounts/test_account_id_123/freeze', $lastRequest['endpoint']);
        $this->assertTrue($lastRequest['data']['freeze_account']);
    }

    public function test_unfreeze_account(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $result = $this->accounts->unfreezeAccount('test_account_id_123');

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertFalse($lastRequest['data']['freeze_account']);
    }

    public function test_get_limits(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountLimitsResponse());

        $limits = $this->accounts->getLimits('test_account_id_123');

        $this->assertInstanceOf(AccountLimits::class, $limits);
        $this->assertEquals(10000.00, $limits->achDailyLimit);
        $this->assertEquals(5000.00, $limits->achPerTransactionLimit);
        $this->assertEquals(9000.00, $limits->getRemainingDailyLimit());

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('accounts/limits/test_account_id_123', $lastRequest['endpoint']);
    }

    public function test_create_limits(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountLimitsResponse());

        $request = new CreateLimitsRequest(
            achDailyLimit: 10000.00,
            achPerTransactionLimit: 5000.00
        );
        $limits = $this->accounts->createLimits('test_account_id_123', $request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('POST', $lastRequest['method']);
        $this->assertEquals(10000.00, $lastRequest['data']['ach_daily_limit']);
    }

    public function test_update_limits(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::accountLimitsResponse([
            'ach_daily_limit' => 20000.00,
        ]));

        $request = new UpdateLimitsRequest(achDailyLimit: 20000.00);
        $limits = $this->accounts->updateLimits('test_account_id_123', $request);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('PATCH', $lastRequest['method']);
    }

    public function test_delete_limits(): void
    {
        $this->httpClient->addMockResponse(200, ['success' => true]);

        $result = $this->accounts->deleteLimits('test_account_id_123');

        $this->assertTrue($result);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest['method']);
        $this->assertEquals('accounts/limits/test_account_id_123', $lastRequest['endpoint']);
    }

    public function test_get_transactions(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::transactionResponse(),
            TestDataFactory::transactionResponse(['id' => 'tx_2', 'amount' => -200.00]),
        ]);

        $request = GetTransactionsRequest::forDateRange('2024-01-01', '2024-12-31');
        $transactions = $this->accounts->getTransactions('test_account_id_123', $request);

        $this->assertCount(2, $transactions);
        $this->assertInstanceOf(Transaction::class, $transactions[0]);
        $this->assertEquals(-100.00, $transactions[0]->amount);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('2024-01-01', $lastRequest['query']['start_date']);
        $this->assertEquals('2024-12-31', $lastRequest['query']['end_date']);
    }

    public function test_get_recent_transactions(): void
    {
        $this->httpClient->addMockResponse(200, [
            TestDataFactory::transactionResponse(),
        ]);

        $transactions = $this->accounts->getRecentTransactions('test_account_id_123', 30);

        $this->assertCount(1, $transactions);

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertArrayHasKey('start_date', $lastRequest['query']);
        $this->assertArrayHasKey('end_date', $lastRequest['query']);
    }

    public function test_get_transaction(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transactionResponse());

        $transaction = $this->accounts->getTransaction('test_account_id_123', 'tx_123');

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertTrue($transaction->isCompleted());

        $lastRequest = $this->httpClient->getLastRequest();
        $this->assertEquals('accounts/transactions/test_account_id_123/tx_123', $lastRequest['endpoint']);
    }

    public function test_transaction_status_helpers(): void
    {
        $this->httpClient->addMockResponse(200, TestDataFactory::transactionResponse([
            'status_code' => 'P',
            'credit_or_debit' => 'CREDIT',
        ]));

        $transaction = $this->accounts->getTransaction('acc', 'tx');

        $this->assertTrue($transaction->isPending());
        $this->assertFalse($transaction->isCompleted());
        $this->assertTrue($transaction->isCredit());
        $this->assertFalse($transaction->isDebit());
    }
}
