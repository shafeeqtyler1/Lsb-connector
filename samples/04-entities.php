<?php

/**
 * LSBX Connector SDK - Entity Management Example
 *
 * Entities represent external bank accounts used for ACH transfers.
 * This example demonstrates how to create, search, update, and delete entities.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ShafeeqKt\LsbConnector\LsbxClient;
use ShafeeqKt\LsbConnector\DTO\Request\Entity\CreateEntityRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Entity\UpdateEntityRequest;
use ShafeeqKt\LsbConnector\DTO\Request\Entity\SearchEntityRequest;
use ShafeeqKt\LsbConnector\Exceptions\LsbxException;

// Initialize client
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');

// =============================================================================
// Create an Entity (Person Account)
// =============================================================================

echo "Creating an entity for a person...\n";

$personEntityRequest = new CreateEntityRequest(
    accountNumber: '123456789',
    routingNumber: '021000021',
    accountHolderName: 'John Doe',
    accountType: CreateEntityRequest::ACCOUNT_TYPE_CHECKING,
    description: 'John\'s External Checking Account'
);

try {
    $entity = $client->entities()->create($personEntityRequest);

    echo "Entity Created!\n";
    echo "  Entity ID: {$entity->id}\n";
    echo "  Account Number: {$entity->accountNumber}\n";
    echo "  Routing Number: {$entity->routingNumber}\n";
    echo "  Account Holder: {$entity->accountHolderName}\n";
    echo "  Is Organization: " . ($entity->isOrganization ? 'Yes' : 'No') . "\n";
    echo "  Is Checking: " . ($entity->isChecking() ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Failed to create entity: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create an Entity (Organization Account)
// =============================================================================

echo "\nCreating an entity for an organization...\n";

$orgEntityRequest = new CreateEntityRequest(
    accountNumber: '987654321',
    routingNumber: '021000021',
    accountHolderName: 'Acme Corp LLC',
    accountType: CreateEntityRequest::ACCOUNT_TYPE_CHECKING,
    description: 'Acme Corp Operating Account',
    isOrganization: true
);

try {
    $orgEntity = $client->entities()->create($orgEntityRequest);

    echo "Organization Entity Created!\n";
    echo "  Entity ID: {$orgEntity->id}\n";
    echo "  Account Holder: {$orgEntity->accountHolderName}\n";
    echo "  Is Organization: " . ($orgEntity->isOrganization ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Failed to create organization entity: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create Entity with Idempotency Key (Safe Retries)
// =============================================================================

echo "\nCreating entity with idempotency key...\n";

$idempotencyKey = 'entity-' . md5('123456789-021000021');

try {
    // This request can be safely retried
    $entity = $client->entities()->create($personEntityRequest, $idempotencyKey);
    echo "Entity created with idempotency key: {$entity->id}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create Entity with Savings Account Type
// =============================================================================

echo "\nCreating a savings account entity...\n";

$savingsEntityRequest = new CreateEntityRequest(
    accountNumber: '555555555',
    routingNumber: '021000021',
    accountHolderName: 'Jane Smith',
    accountType: CreateEntityRequest::ACCOUNT_TYPE_SAVINGS,
    description: 'Jane\'s External Savings Account'
);

try {
    $savingsEntity = $client->entities()->create($savingsEntityRequest);

    echo "Savings Entity Created!\n";
    echo "  Entity ID: {$savingsEntity->id}\n";
    echo "  Is Savings: " . ($savingsEntity->isSavings() ? 'Yes' : 'No') . "\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// List All Entities
// =============================================================================

echo "\nListing all entities...\n";

try {
    // List with pagination
    $entities = $client->entities()->list(pageNumber: 0, recordsPerPage: 20);

    echo "Found " . count($entities) . " entities:\n";
    foreach ($entities as $entity) {
        $type = $entity->isOrganization ? 'ORG' : 'PERSON';
        $accountType = $entity->isChecking() ? 'Checking' : 'Savings';
        echo "  [{$type}] {$entity->id} - {$entity->accountHolderName} ({$accountType})\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Search for Entities
// =============================================================================

echo "\nSearching for entities...\n";

try {
    // Search by account and routing number
    $searchRequest = SearchEntityRequest::byAccountAndRouting('123456789', '021000021');
    $results = $client->entities()->search($searchRequest);

    echo "Search found " . count($results) . " entities\n";

    // Convenience method for finding a single entity
    $entity = $client->entities()->findByAccountAndRouting('123456789', '021000021');

    if ($entity) {
        echo "Found entity: {$entity->id}\n";
        echo "  Account Holder: {$entity->accountHolderName}\n";
        echo "  Financial Institution: {$entity->financialInstitutionName}\n";
    } else {
        echo "Entity not found\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Update an Entity
// =============================================================================

$entityId = 'entity_id_here'; // Replace with actual entity ID

echo "\nUpdating entity...\n";

try {
    $updateRequest = new UpdateEntityRequest(
        description: 'Updated Description',
        accountHolderName: 'John M Doe'
    );

    $updated = $client->entities()->update($entityId, $updateRequest);

    echo "Entity Updated!\n";
    echo "  New Description: {$updated->description}\n";
    echo "  New Account Holder: {$updated->accountHolderName}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Delete an Entity
// =============================================================================

echo "\nDeleting entity...\n";

try {
    $deleted = $client->entities()->delete($entityId);

    if ($deleted) {
        echo "Entity deleted successfully!\n";
    } else {
        echo "Failed to delete entity\n";
    }
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// Note About Account Holder Name Sanitization
// =============================================================================

echo "\n--- Account Holder Name Sanitization ---\n";
echo "The SDK automatically sanitizes account holder names:\n";
echo "  - Maximum 22 characters\n";
echo "  - Alphanumeric characters and spaces only\n";
echo "  - Special characters are removed\n\n";

$testRequest = new CreateEntityRequest(
    accountNumber: '111111111',
    routingNumber: '021000021',
    accountHolderName: 'John@Doe#With$Special!Characters&More'
);

$data = $testRequest->toArray();
echo "Original: 'John@Doe#With\$Special!Characters&More'\n";
echo "Sanitized: '{$data['account_holder_name']}'\n";

echo "\nEntity management examples complete!\n";
