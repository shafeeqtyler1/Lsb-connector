<?php

/**
 * LSBX Connector SDK - Webhook Management Example
 *
 * This example demonstrates how to manage webhooks and handle webhook events.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Shafeeq\LsbConnector\LsbxClient;
use Shafeeq\LsbConnector\DTO\Request\Webhook\CreateWebhookRequest;
use Shafeeq\LsbConnector\DTO\Request\Webhook\UpdateWebhookRequest;
use Shafeeq\LsbConnector\Resources\Webhooks;
use Shafeeq\LsbConnector\Exceptions\LsbxException;

// Initialize client
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');

// =============================================================================
// Available Webhook Event Scopes
// =============================================================================

echo "Available Webhook Event Scopes:\n";
echo "  - " . Webhooks::SCOPE_ACCOUNT_DEPOSIT . "\n";
echo "  - " . Webhooks::SCOPE_ACCOUNT_DEPOSIT_NOTICE . "\n";
echo "  - " . Webhooks::SCOPE_ACCOUNT_DEPOSIT_STATUS . "\n";
echo "  - " . Webhooks::SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS . "\n";
echo "  - " . Webhooks::SCOPE_CUSTOMER . "\n";

// =============================================================================
// Get Available Event Scopes from API
// =============================================================================

echo "\nGetting available event scopes from API...\n";

try {
    $scopes = $client->webhooks()->getEventScopes();

    echo "API returned " . count($scopes) . " event scopes:\n";
    foreach ($scopes as $scope) {
        echo "  - {$scope}\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create a Webhook
// =============================================================================

echo "\nCreating a webhook...\n";

try {
    $webhookRequest = new CreateWebhookRequest(
        url: 'https://your-domain.com/api/webhooks/lsbx',
        eventScopes: [
            Webhooks::SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS,
            Webhooks::SCOPE_CUSTOMER,
        ],
        signingSecret: 'your-secure-signing-secret-key'
    );

    $webhook = $client->webhooks()->create($webhookRequest);

    echo "Webhook Created!\n";
    echo "  Webhook ID: {$webhook->id}\n";
    echo "  URL: {$webhook->url}\n";
    echo "  Status: {$webhook->status}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// List All Webhooks
// =============================================================================

echo "\nListing all webhooks...\n";

try {
    $webhooks = $client->webhooks()->list();

    echo "Found " . count($webhooks) . " webhooks:\n";
    foreach ($webhooks as $webhook) {
        echo "  [{$webhook->status}] {$webhook->id} - {$webhook->url}\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Update a Webhook
// =============================================================================

$webhookId = 'webhook_id_here'; // Replace with actual webhook ID

echo "\nUpdating webhook...\n";

try {
    $updateRequest = new UpdateWebhookRequest(
        url: 'https://your-domain.com/api/webhooks/lsbx-updated',
        eventScopes: [
            Webhooks::SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS,
            Webhooks::SCOPE_ACCOUNT_DEPOSIT_STATUS,
            Webhooks::SCOPE_CUSTOMER,
        ]
    );

    $updated = $client->webhooks()->update($webhookId, $updateRequest);

    echo "Webhook Updated!\n";
    echo "  New URL: {$updated->url}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Delete a Webhook
// =============================================================================

echo "\nDeleting webhook...\n";

try {
    $deleted = $client->webhooks()->delete($webhookId);

    if ($deleted) {
        echo "Webhook deleted successfully!\n";
    } else {
        echo "Failed to delete webhook\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Webhook Handler Example (For Your Endpoint)
// =============================================================================

echo "\n";
echo "=============================================================================\n";
echo "WEBHOOK HANDLER EXAMPLE\n";
echo "=============================================================================\n";
echo "\n";
echo "Copy this code to your webhook endpoint (e.g., /api/webhooks/lsbx):\n\n";

$handlerCode = <<<'PHP'
<?php

use Shafeeq\LsbConnector\LsbxClient;

// Initialize client
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');

// Your webhook signing secret
$signingSecret = 'your-secure-signing-secret-key';

// Get the raw payload and signature
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LSBX_SIGNATURE'] ?? '';

// Verify the signature
if (!$client->webhooks()->verifySignature($payload, $signature, $signingSecret)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Parse the events (handles both single events and batch delivery)
$events = $client->webhooks()->parseEvents($payload);

foreach ($events as $event) {
    // Log the event
    error_log("Received webhook event: {$event->eventCode} - {$event->action}");

    switch ($event->eventCode) {
        case 'DEPOSIT_ACCOUNT_TRANSACTION_TRANSFER':
            // Handle transaction events
            handleTransactionEvent($event);
            break;

        case 'DEPOSIT_ACCOUNT_TRANSACTION_STATUS':
            // Handle transaction status updates
            handleTransactionStatusEvent($event);
            break;

        case 'CUSTOMER':
            // Handle customer events
            handleCustomerEvent($event);
            break;

        default:
            // Log unknown event types
            error_log("Unknown event code: {$event->eventCode}");
    }
}

// Respond with success
http_response_code(200);
echo json_encode(['success' => true]);

// Event handler functions
function handleTransactionEvent($event) {
    $accountId = $event->accountId;
    $data = $event->data;

    if ($event->isCreated()) {
        // New transaction created
        echo "New transaction on account: {$accountId}\n";
    } elseif ($event->isUpdated()) {
        // Transaction updated
        echo "Transaction updated on account: {$accountId}\n";
    }
}

function handleTransactionStatusEvent($event) {
    $data = $event->data;
    $status = $data['status'] ?? 'unknown';

    echo "Transaction status changed to: {$status}\n";
}

function handleCustomerEvent($event) {
    $customerId = $event->customerId;

    if ($event->isCreated()) {
        echo "New customer created: {$customerId}\n";
    } elseif ($event->isUpdated()) {
        echo "Customer updated: {$customerId}\n";
    } elseif ($event->isDeleted()) {
        echo "Customer deleted: {$customerId}\n";
    }
}
PHP;

echo $handlerCode;
echo "\n\n";

// =============================================================================
// Signature Verification Example
// =============================================================================

echo "=============================================================================\n";
echo "SIGNATURE VERIFICATION EXAMPLE\n";
echo "=============================================================================\n\n";

$secret = 'my-webhook-secret';
$payload = '{"event_id":"123","event_code":"CUSTOMER","action":"CREATED"}';

// Generate a signature (this is what LSBX would send)
$signature = hash_hmac('sha256', $payload, $secret);
echo "Payload: {$payload}\n";
echo "Secret: {$secret}\n";
echo "Generated Signature: {$signature}\n\n";

// Verify the signature
$isValid = $client->webhooks()->verifySignature($payload, $signature, $secret);
echo "Signature Valid: " . ($isValid ? 'Yes' : 'No') . "\n";

// Test with wrong signature
$wrongSignature = 'invalid-signature';
$isInvalid = $client->webhooks()->verifySignature($payload, $wrongSignature, $secret);
echo "Wrong Signature Valid: " . ($isInvalid ? 'Yes' : 'No') . "\n";

// =============================================================================
// Parsing Webhook Events Example
// =============================================================================

echo "\n";
echo "=============================================================================\n";
echo "PARSING WEBHOOK EVENTS EXAMPLE\n";
echo "=============================================================================\n\n";

// Single event payload
$singleEventPayload = json_encode([
    'event_id' => 'evt_123',
    'event_code' => 'DEPOSIT_ACCOUNT_TRANSACTION_TRANSFER',
    'action' => 'CREATED',
    'account_id' => 'acc_456',
    'customer_id' => 'cust_789',
    'data' => [
        'transaction_id' => 'txn_abc',
        'amount' => 100.00,
        'description' => 'ACH Deposit',
    ],
]);

echo "Single Event Payload:\n";
$event = $client->webhooks()->parseEvent($singleEventPayload);
echo "  Event ID: {$event->eventId}\n";
echo "  Event Code: {$event->eventCode}\n";
echo "  Action: {$event->action}\n";
echo "  Account ID: {$event->accountId}\n";
echo "  Is Created: " . ($event->isCreated() ? 'Yes' : 'No') . "\n";
echo "  Data: " . json_encode($event->data) . "\n";

// Batch events payload
$batchEventsPayload = json_encode([
    [
        'event_id' => 'evt_001',
        'event_code' => 'CUSTOMER',
        'action' => 'CREATED',
        'customer_id' => 'cust_123',
    ],
    [
        'event_id' => 'evt_002',
        'event_code' => 'CUSTOMER',
        'action' => 'UPDATED',
        'customer_id' => 'cust_456',
    ],
]);

echo "\nBatch Events Payload:\n";
$events = $client->webhooks()->parseEvents($batchEventsPayload);
echo "  Received " . count($events) . " events\n";
foreach ($events as $index => $event) {
    echo "  Event " . ($index + 1) . ": {$event->eventCode} - {$event->action}\n";
}

echo "\nWebhook examples complete!\n";
