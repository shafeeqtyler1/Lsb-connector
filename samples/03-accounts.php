<?php

/**
 * LSBX Connector SDK - Account Operations Example
 *
 * This example demonstrates how to manage bank accounts, limits, and transactions.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Shafeeq\LsbConnector\LsbxClient;
use Shafeeq\LsbConnector\DTO\Request\Account\CreateAccountRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\UpdateAccountRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\CreateLimitsRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\UpdateLimitsRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\GetTransactionsRequest;
use Shafeeq\LsbConnector\Exceptions\LsbxException;

// Initialize client
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');

$customerId = '109392'; // Replace with actual customer ID

// =============================================================================
// Create a Checking Account
// =============================================================================

echo "Creating a checking account...\n";

$accountRequest = new CreateAccountRequest(
    type: CreateAccountRequest::TYPE_PERSON,
    customerId: $customerId,
    productType: CreateAccountRequest::PRODUCT_TYPE_CHECKING,
    productCode: 'FREE',
    ownershipType: CreateAccountRequest::OWNERSHIP_SINGLE,
    description: 'Primary Checking Account'
);

try {
    $account = $client->accounts()->create($accountRequest);

    echo "Account Created!\n";
    echo "  Account ID: {$account->id}\n";
    echo "  Product Type: {$account->productType}\n";
    echo "  Status: {$account->currentAccountStatusCode}\n";
} catch (LsbxException $e) {
    echo "Failed to create account: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create Accounts Using Convenience Methods
// =============================================================================

echo "\nCreating accounts with convenience methods...\n";

try {
    // Create a checking account (simplified)
    $checking = $client->accounts()->create(
        CreateAccountRequest::checking($customerId, 'Secondary Checking')
    );
    echo "Checking account created: {$checking->id}\n";

    // Create a savings account (simplified)
    $savings = $client->accounts()->create(
        CreateAccountRequest::savings($customerId, 'Emergency Savings')
    );
    echo "Savings account created: {$savings->id}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Get Account Information
// =============================================================================

$accountId = 'your_account_id'; // Replace with actual account ID

echo "\nGetting account information...\n";

try {
    // Get account metadata
    $account = $client->accounts()->get($accountId);

    echo "Account Details:\n";
    echo "  ID: {$account->id}\n";
    echo "  Balance: \${$account->balance}\n";
    echo "  Available Balance: \${$account->availableBalance}\n";
    echo "  Status: {$account->currentAccountStatusCode}\n";
    echo "  Product Type: {$account->productType}\n";
    echo "  Is Open: " . ($account->isOpen() ? 'Yes' : 'No') . "\n";
    echo "  Is Frozen: " . ($account->isFrozen() ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Get Banking Details (Account/Routing Numbers)
// =============================================================================

echo "\nGetting banking details...\n";

try {
    $details = $client->accounts()->getDetails($accountId);

    echo "Banking Details:\n";
    echo "  Account Number: {$details->accountNumber}\n";
    echo "  Routing Number: {$details->routingNumber}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Update Account
// =============================================================================

echo "\nUpdating account...\n";

try {
    $updateRequest = new UpdateAccountRequest(
        description: 'Updated Account Description'
    );

    $updated = $client->accounts()->update($accountId, $updateRequest);
    echo "Account updated successfully!\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Freeze and Unfreeze Account
// =============================================================================

echo "\nFreezing and unfreezing account...\n";

try {
    // Freeze the account
    $frozenAccount = $client->accounts()->freezeAccount($accountId);
    echo "Account frozen: " . ($frozenAccount->isFrozen() ? 'Yes' : 'No') . "\n";

    // Unfreeze the account
    $unfrozenAccount = $client->accounts()->unfreezeAccount($accountId);
    echo "Account unfrozen: " . ($unfrozenAccount->isOpen() ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Manage Account Limits
// =============================================================================

echo "\nManaging account limits...\n";

try {
    // Create limits
    $limitsRequest = new CreateLimitsRequest(
        achDailyLimit: 10000.00,
        achPerTransactionLimit: 5000.00,
        achMonthlyLimit: 50000.00
    );

    $limits = $client->accounts()->createLimits($accountId, $limitsRequest);
    echo "Limits created!\n";
    echo "  ACH Daily Limit: \${$limits->achDailyLimit}\n";
    echo "  ACH Per Transaction: \${$limits->achPerTransactionLimit}\n";

    // Get current limits
    $currentLimits = $client->accounts()->getLimits($accountId);
    echo "\nCurrent Limits:\n";
    echo "  ACH Daily Limit: \${$currentLimits->achDailyLimit}\n";
    echo "  Available Daily: \${$currentLimits->availableAchDailyLimit}\n";

    // Update limits
    $updateLimits = new UpdateLimitsRequest(
        achDailyLimit: 20000.00
    );
    $updatedLimits = $client->accounts()->updateLimits($accountId, $updateLimits);
    echo "\nLimits updated! New daily limit: \${$updatedLimits->achDailyLimit}\n";

    // Delete limits (optional)
    // $client->accounts()->deleteLimits($accountId);
    // echo "Limits deleted!\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Get Transactions
// =============================================================================

echo "\nGetting transactions...\n";

try {
    // Get transactions for a date range
    $transactionRequest = GetTransactionsRequest::forDateRange('2024-01-01', '2024-12-31');
    $transactions = $client->accounts()->getTransactions($accountId, $transactionRequest);

    echo "Found " . count($transactions) . " transactions:\n";
    foreach ($transactions as $tx) {
        $sign = $tx->isDebit() ? '-' : '+';
        echo "  [{$tx->effectiveDate}] {$sign}\${$tx->amount} - {$tx->description}\n";
    }

    // Get recent transactions (last 30 days)
    $recentTransactions = $client->accounts()->getRecentTransactions($accountId, 30);
    echo "\nRecent transactions (last 30 days): " . count($recentTransactions) . "\n";

    // Get specific transaction
    $transactionId = 'transaction_id'; // Replace with actual transaction ID
    $transaction = $client->accounts()->getTransaction($accountId, $transactionId);

    if ($transaction) {
        echo "\nTransaction Details:\n";
        echo "  ID: {$transaction->transactionId}\n";
        echo "  Amount: \${$transaction->amount}\n";
        echo "  Description: {$transaction->description}\n";
        echo "  Status: {$transaction->statusCode}\n";
        echo "  Is Completed: " . ($transaction->isCompleted() ? 'Yes' : 'No') . "\n";
        echo "  Is Pending: " . ($transaction->isPending() ? 'Yes' : 'No') . "\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nAccount operations examples complete!\n";
