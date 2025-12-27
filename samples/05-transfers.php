<?php

/**
 * LSBX Connector SDK - Transfers Example
 *
 * This example demonstrates how to create ACH, Book, and Wire transfers.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Shafeeq\LsbConnector\LsbxClient;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CancelAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateBookTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateWireTransferRequest;
use Shafeeq\LsbConnector\DTO\Common\Address;
use Shafeeq\LsbConnector\Exceptions\LsbxException;

// Initialize client
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');

// Replace with actual IDs
$accountId = 'your_account_id';
$entityId = 'external_entity_id';

// =============================================================================
// ACH Transfers - Debit (Pull Funds)
// =============================================================================

echo "Creating ACH Debit transfer (pull funds from external account)...\n";

try {
    // Method 1: Using convenience method
    $transfer = $client->transfers()->debit(
        accountId: $accountId,
        entityId: $entityId,
        amount: 100.00,
        description: 'Payment collection',           // Internal description (max 100 chars)
        sameDayAch: false,
        externalDescription: 'ACME Payment'          // Bank statement description (max 32 chars)
    );

    echo "ACH Debit Transfer Created!\n";
    echo "  Transfer ID: {$transfer->id}\n";
    echo "  Amount: \${$transfer->amount}\n";
    echo "  Status: {$transfer->status}\n";
    echo "  Is Pending: " . ($transfer->isPending() ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// ACH Transfers - Credit (Push Funds)
// =============================================================================

echo "\nCreating ACH Credit transfer (push funds to external account)...\n";

try {
    // Method 1: Using convenience method
    $transfer = $client->transfers()->credit(
        accountId: $accountId,
        entityId: $entityId,
        amount: 250.00,
        description: 'Vendor payment',              // Internal description (max 100 chars)
        sameDayAch: true,                           // Same-day ACH (faster, may have additional fees)
        externalDescription: 'Vendor Pmt 12345'    // Bank statement description (max 32 chars)
    );

    echo "ACH Credit Transfer Created!\n";
    echo "  Transfer ID: {$transfer->id}\n";
    echo "  Amount: \${$transfer->amount}\n";
    echo "  Is Credit: " . ($transfer->isCredit() ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// ACH Transfers - Full Control with Request Object
// =============================================================================

echo "\nCreating ACH transfer with full request object...\n";

try {
    $achRequest = new CreateAchTransferRequest(
        accountId: $accountId,
        entityId: $entityId,
        type: CreateAchTransferRequest::TYPE_DEBIT,
        amount: 500.00,
        description: 'Monthly subscription',
        sameDayAch: false,
        externalDescription: 'ACME SUB 12/24' // Description shown on bank statement
    );

    // With idempotency key for safe retries
    $idempotencyKey = 'ach-' . uniqid();
    $transfer = $client->transfers()->createAch($achRequest, $idempotencyKey);

    echo "ACH Transfer Created!\n";
    echo "  Transfer ID: {$transfer->id}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Get Pending ACH Transfers
// =============================================================================

echo "\nGetting pending ACH transfers...\n";

try {
    $pendingTransfers = $client->transfers()->getPendingAch($accountId);

    echo "Found " . count($pendingTransfers) . " pending ACH transfers:\n";
    foreach ($pendingTransfers as $transfer) {
        $type = $transfer->isDebit() ? 'DEBIT' : 'CREDIT';
        echo "  [{$type}] {$transfer->id} - \${$transfer->amount} - {$transfer->status}\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Cancel ACH Transfer
// =============================================================================

$transferId = 'pending_transfer_id'; // Replace with actual pending transfer ID

echo "\nCanceling ACH transfer...\n";

try {
    // Method 1: Using convenience method
    $cancelled = $client->transfers()->cancelAchById($transferId, $accountId);

    if ($cancelled) {
        echo "Transfer cancelled successfully!\n";
    } else {
        echo "Failed to cancel transfer\n";
    }

    // Method 2: Using request object
    $cancelRequest = new CancelAchTransferRequest(
        transferId: $transferId,
        accountId: $accountId
    );
    $cancelled = $client->transfers()->cancelAch($cancelRequest);
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Book Transfers (Internal Transfers Between Accounts)
// =============================================================================

$fromAccountId = 'source_account_id';
$toAccountId = 'destination_account_id';

echo "\nCreating Book transfer (internal transfer)...\n";

try {
    // Method 1: Using convenience method
    $transfer = $client->transfers()->internalTransfer(
        fromAccountId: $fromAccountId,
        toAccountId: $toAccountId,
        amount: 1000.00,
        description: 'Transfer to savings'
    );

    echo "Book Transfer Created!\n";
    echo "  Transfer ID: {$transfer->id}\n";
    echo "  Amount: \${$transfer->amount}\n";
    echo "  Status: {$transfer->status}\n";

    // Method 2: Using request object
    $bookRequest = new CreateBookTransferRequest(
        fromAccountId: $fromAccountId,
        toAccountId: $toAccountId,
        amount: 500.00,
        description: 'Monthly savings contribution'
    );

    $transfer = $client->transfers()->createBook($bookRequest);
    echo "Another Book Transfer Created: {$transfer->id}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Wire Transfers (Sandbox Only)
// =============================================================================

echo "\nCreating Wire transfer to individual (Sandbox only)...\n";

try {
    // Wire transfer to an individual (use first_name + last_name)
    $wireRequest = CreateWireTransferRequest::toIndividual(
        accountId: $accountId,
        entityId: $entityId,
        amount: 10000.00,
        effectiveDate: date('Y-m-d', strtotime('+1 day')),
        firstName: 'John',
        lastName: 'Doe',
        recipientAddress: new Address(
            street: '456 Main St',
            city: 'New York',
            state: 'NY',
            postalCode: '10001'
        ),
        description: 'Wire payment to individual',
        externalDescription: 'Wire Payment'
    );

    $wireTransfer = $client->transfers()->createWire($wireRequest);

    echo "Wire Transfer to Individual Created!\n";
    echo "  Transfer ID: {$wireTransfer->id}\n";
    echo "  Amount: \${$wireTransfer->amount}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nCreating Wire transfer to business (Sandbox only)...\n";

try {
    // Wire transfer to a business (use name only)
    $wireRequest = CreateWireTransferRequest::toBusiness(
        accountId: $accountId,
        entityId: $entityId,
        amount: 50000.00,
        effectiveDate: date('Y-m-d', strtotime('+1 day')),
        businessName: 'Acme Corporation LLC',
        recipientAddress: new Address(
            street: '789 Business Ave',
            city: 'Los Angeles',
            state: 'CA',
            postalCode: '90001'
        ),
        description: 'Wire payment to business',
        externalDescription: 'Vendor Payment'
    );

    $wireTransfer = $client->transfers()->createWire($wireRequest);

    echo "Wire Transfer to Business Created!\n";
    echo "  Transfer ID: {$wireTransfer->id}\n";
    echo "  Amount: \${$wireTransfer->amount}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Get Pending Wire Transfers (Sandbox Only)
// =============================================================================

echo "\nGetting pending Wire transfers...\n";

try {
    $pendingWires = $client->transfers()->getPendingWire($accountId);

    echo "Found " . count($pendingWires) . " pending Wire transfers:\n";
    foreach ($pendingWires as $transfer) {
        echo "  {$transfer->id} - \${$transfer->amount}\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Transfer with Idempotency (Safe Retries)
// =============================================================================

echo "\n--- Idempotency Example ---\n";
echo "Using idempotency keys ensures safe retries:\n\n";

$idempotencyKey = 'payment-' . date('Y-m-d') . '-invoice-12345';

try {
    // First request
    $transfer1 = $client->transfers()->debit(
        accountId: $accountId,
        entityId: $entityId,
        amount: 99.99,
        description: 'Invoice #12345 payment',
        idempotencyKey: $idempotencyKey
    );
    echo "First request - Transfer ID: {$transfer1->id}\n";

    // Retry with same idempotency key (returns same transfer, no duplicate)
    $transfer2 = $client->transfers()->debit(
        accountId: $accountId,
        entityId: $entityId,
        amount: 99.99,
        description: 'Invoice #12345 payment',
        idempotencyKey: $idempotencyKey
    );
    echo "Retry request - Transfer ID: {$transfer2->id}\n";
    echo "Same transfer returned: " . ($transfer1->id === $transfer2->id ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTransfer examples complete!\n";
