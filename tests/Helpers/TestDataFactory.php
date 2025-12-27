<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Tests\Helpers;

use Shafeeq\LsbConnector\DTO\Common\Address;
use Shafeeq\LsbConnector\DTO\Common\Phone;
use Shafeeq\LsbConnector\DTO\Common\Identification;
use Shafeeq\LsbConnector\DTO\Common\PersonDetails;
use Shafeeq\LsbConnector\DTO\Common\OrganizationDetails;
use Shafeeq\LsbConnector\DTO\Common\CddQuestion;
use Shafeeq\LsbConnector\DTO\Request\Customer\CreatePersonCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\CreateOrganizationCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\CreateAccountRequest;
use Shafeeq\LsbConnector\DTO\Request\Entity\CreateEntityRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateBookTransferRequest;

class TestDataFactory
{
    public static function address(array $overrides = []): Address
    {
        return new Address(
            street: $overrides['street'] ?? '123 Test Street',
            city: $overrides['city'] ?? 'Test City',
            state: $overrides['state'] ?? 'NY',
            postalCode: $overrides['postalCode'] ?? '10001',
            country: $overrides['country'] ?? 'USA',
            streetLine2: $overrides['streetLine2'] ?? null,
            region: $overrides['region'] ?? null
        );
    }

    public static function phone(array $overrides = []): Phone
    {
        return new Phone(
            number: $overrides['number'] ?? '5551234567',
            countryCode: $overrides['countryCode'] ?? 'USA'
        );
    }

    public static function identification(array $overrides = []): Identification
    {
        return new Identification(
            type: $overrides['type'] ?? Identification::TYPE_DRIVERS_LICENSE,
            number: $overrides['number'] ?? '12345678',
            issueDate: $overrides['issueDate'] ?? '2020-01-01',
            expireDate: $overrides['expireDate'] ?? '2030-01-01',
            countryCode: $overrides['countryCode'] ?? 'USA'
        );
    }

    public static function personDetails(array $overrides = []): PersonDetails
    {
        return new PersonDetails(
            firstName: $overrides['firstName'] ?? 'John',
            lastName: $overrides['lastName'] ?? 'Doe',
            birthDate: $overrides['birthDate'] ?? '1990-01-15',
            address: $overrides['address'] ?? self::address(),
            phone: $overrides['phone'] ?? self::phone(),
            identification: $overrides['identification'] ?? self::identification(),
            taxId: $overrides['taxId'] ?? '123456789',
            email: $overrides['email'] ?? 'john.doe@test.com',
            occupationCode: $overrides['occupationCode'] ?? '15-0000',
            middleName: $overrides['middleName'] ?? null
        );
    }

    public static function organizationDetails(array $overrides = []): OrganizationDetails
    {
        return new OrganizationDetails(
            name: $overrides['name'] ?? 'Test Corp LLC',
            formationDate: $overrides['formationDate'] ?? '2020-01-15',
            address: $overrides['address'] ?? self::address(),
            phone: $overrides['phone'] ?? self::phone(),
            taxId: $overrides['taxId'] ?? '987654321',
            email: $overrides['email'] ?? 'contact@testcorp.com',
            naicsCode: $overrides['naicsCode'] ?? '541511',
            dbaName: $overrides['dbaName'] ?? 'Test Corp'
        );
    }

    public static function createPersonCustomerRequest(array $overrides = []): CreatePersonCustomerRequest
    {
        return new CreatePersonCustomerRequest(
            personDetails: $overrides['personDetails'] ?? self::personDetails(),
            cddQuestions: $overrides['cddQuestions'] ?? [CddQuestion::notPoliticallyExposed()]
        );
    }

    public static function createOrganizationCustomerRequest(array $overrides = []): CreateOrganizationCustomerRequest
    {
        return new CreateOrganizationCustomerRequest(
            organizationDetails: $overrides['organizationDetails'] ?? self::organizationDetails()
        );
    }

    public static function createAccountRequest(array $overrides = []): CreateAccountRequest
    {
        return new CreateAccountRequest(
            type: $overrides['type'] ?? CreateAccountRequest::TYPE_PERSON,
            customerId: $overrides['customerId'] ?? '12345',
            productType: $overrides['productType'] ?? CreateAccountRequest::PRODUCT_TYPE_CHECKING,
            productCode: $overrides['productCode'] ?? 'FREE',
            ownershipType: $overrides['ownershipType'] ?? CreateAccountRequest::OWNERSHIP_SINGLE,
            description: $overrides['description'] ?? 'Test Account'
        );
    }

    public static function createEntityRequest(array $overrides = []): CreateEntityRequest
    {
        return new CreateEntityRequest(
            accountNumber: $overrides['accountNumber'] ?? '123456789',
            routingNumber: $overrides['routingNumber'] ?? '021000021',
            accountHolderName: $overrides['accountHolderName'] ?? 'Test Account Holder',
            accountType: $overrides['accountType'] ?? CreateEntityRequest::ACCOUNT_TYPE_CHECKING,
            description: $overrides['description'] ?? 'Test Entity',
            customString: $overrides['customString'] ?? null
        );
    }

    public static function createAchTransferRequest(array $overrides = []): CreateAchTransferRequest
    {
        return new CreateAchTransferRequest(
            accountId: $overrides['accountId'] ?? 'test_account_id',
            entityId: $overrides['entityId'] ?? 'test_entity_id',
            type: $overrides['type'] ?? CreateAchTransferRequest::TYPE_DEBIT,
            amount: $overrides['amount'] ?? 100.00,
            description: $overrides['description'] ?? 'Test Transfer',
            sameDayAch: $overrides['sameDayAch'] ?? false,
            externalDescription: $overrides['externalDescription'] ?? null
        );
    }

    public static function createBookTransferRequest(array $overrides = []): CreateBookTransferRequest
    {
        return new CreateBookTransferRequest(
            fromAccountId: $overrides['fromAccountId'] ?? 'from_account_id',
            toAccountId: $overrides['toAccountId'] ?? 'to_account_id',
            amount: $overrides['amount'] ?? 50.00,
            description: $overrides['description'] ?? 'Internal Transfer'
        );
    }

    // API Response data factories

    public static function customerResponse(array $overrides = []): array
    {
        return array_merge([
            'customer_id' => '109392',
            'accounts' => [],
        ], $overrides);
    }

    public static function customerSearchResponse(array $overrides = []): array
    {
        return array_merge([
            'customer_id' => '109392',
            'type' => 'PERSON',
            'person_details' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'birth_date' => '1990-01-15',
                'email' => 'john.doe@test.com',
            ],
            'accounts' => [],
        ], $overrides);
    }

    public static function accountResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'test_account_id_123',
            'customer_id' => '109392',
            'status' => 'ACTIVE',
        ], $overrides);
    }

    public static function accountDetailsResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'test_account_id_123',
            'reporting_for_customer_id' => '109392',
            'balance' => 1000.00,
            'available_balance' => 950.00,
            'product_type_code' => 'CHECKING',
            'current_account_status_code' => 'ACTIVE',
            'is_frozen' => false,
            'currency_code' => 'USD',
        ], $overrides);
    }

    public static function accountBankingDetailsResponse(array $overrides = []): array
    {
        return array_merge([
            'account_number' => '1234567890',
            'routing_number' => '073905527',
        ], $overrides);
    }

    public static function accountLimitsResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'limit_id_123',
            'account_id' => 'test_account_id_123',
            'ach_daily_limit' => 10000.00,
            'ach_per_transaction_limit' => 5000.00,
            'used_ach_daily_limit' => 1000.00,
            'available_ach_daily_limit' => 9000.00,
            'created_date_time' => '2024-01-01T00:00:00+00:00',
        ], $overrides);
    }

    public static function transactionResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'transaction_id_123',
            'account_id' => 'test_account_id_123',
            'transaction_number' => 1001,
            'transaction_type' => 'ACH_DEBIT',
            'type_description' => 'ACH Withdrawal',
            'status_code' => 'C',
            'status_description' => 'Completed',
            'amount' => -100.00,
            'credit_or_debit' => 'DEBIT',
            'running_balance' => 900.00,
            'original_post_date' => '2024-01-15',
            'description' => 'Test Transaction',
        ], $overrides);
    }

    public static function entityResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'entity_id_123',
            'customer_id' => '109392',
            'account_number' => '123456789',
            'routing_number' => '021000021',
            'account_type' => 'Checking',
            'account_holder_name' => 'Test Holder',
            'is_organization' => false,
            'financial_institution_name' => 'Test Bank',
        ], $overrides);
    }

    public static function transferResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'transfer_id_123',
            'account_id' => 'test_account_id_123',
            'type' => 'DEBIT',
            'amount' => 100.00,
            'status' => 'PENDING',
            'effective_date' => '2024-01-20',
            'description' => 'Test Transfer',
        ], $overrides);
    }

    public static function webhookResponse(array $overrides = []): array
    {
        return array_merge([
            'id' => 'webhook_id_123',
            'url' => 'https://example.com/webhook',
            'event_scopes' => ['account.deposit.transactions', 'customer'],
            'created_at' => '2024-01-01T00:00:00+00:00',
        ], $overrides);
    }

    public static function webhookEventPayload(array $overrides = []): array
    {
        return array_merge([
            'event_id' => 'event_id_123',
            'event_date' => '2024-01-15T10:30:00+00:00',
            'event_scope' => 'account.deposit.transactions',
            'event_code' => 'DEPOSIT_ACCOUNT_TRANSACTION_TRANSFER',
            'event_description' => 'An internal or external transfer has been made',
            'action' => 'CREATED',
            'account_id' => 'test_account_id_123',
        ], $overrides);
    }
}
