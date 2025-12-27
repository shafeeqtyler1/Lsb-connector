<?php

/**
 * LSBX Connector SDK - Customer Management Example
 *
 * This example demonstrates how to create, search, and update customers.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Shafeeq\LsbConnector\LsbxClient;
use Shafeeq\LsbConnector\DTO\Request\Customer\CreatePersonCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\CreateOrganizationCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\UpdateCustomerRequest;
use Shafeeq\LsbConnector\DTO\Common\PersonDetails;
use Shafeeq\LsbConnector\DTO\Common\OrganizationDetails;
use Shafeeq\LsbConnector\DTO\Common\Address;
use Shafeeq\LsbConnector\DTO\Common\Phone;
use Shafeeq\LsbConnector\DTO\Common\Identification;
use Shafeeq\LsbConnector\DTO\Common\CddQuestion;
use Shafeeq\LsbConnector\Exceptions\LsbxException;

// Initialize client
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');

// =============================================================================
// Create a Person Customer
// =============================================================================

echo "Creating a person customer...\n";

$personDetails = new PersonDetails(
    firstName: 'John',
    lastName: 'Doe',
    birthDate: '1990-05-15',
    address: new Address(
        street: '123 Main Street',
        city: 'New York',
        state: 'NY',
        postalCode: '10001',
        country: 'USA'
    ),
    phone: new Phone(
        number: '5551234567',
        countryCode: 'USA'
    ),
    identification: new Identification(
        type: Identification::TYPE_DRIVERS_LICENSE,
        number: 'D12345678',
        issueDate: '2020-01-15',
        expireDate: '2028-01-15',
        countryCode: 'USA'
    ),
    taxId: '123456789',
    email: 'john.doe@example.com',
    occupationCode: '15-0000' // Computer and Mathematical Occupations
);

$personRequest = new CreatePersonCustomerRequest(
    personDetails: $personDetails,
    cddQuestions: [
        CddQuestion::notPoliticallyExposed(),
    ]
);

try {
    $customer = $client->customers()->createPerson($personRequest);

    echo "Person Customer Created!\n";
    echo "  Customer ID: {$customer->customerId}\n";
    echo "  Name: {$customer->firstName} {$customer->lastName}\n";
    echo "  Type: " . ($customer->isPerson() ? 'Person' : 'Organization') . "\n";
} catch (LsbxException $e) {
    echo "Failed to create person customer: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create an Organization Customer
// =============================================================================

echo "\nCreating an organization customer...\n";

$organizationDetails = new OrganizationDetails(
    name: 'Acme Corporation LLC',
    formationDate: '2015-03-20',
    address: new Address(
        street: '456 Business Avenue',
        city: 'Los Angeles',
        state: 'CA',
        postalCode: '90001',
        country: 'USA'
    ),
    phone: new Phone(
        number: '5559876543',
        countryCode: 'USA'
    ),
    taxId: '987654321',
    email: 'contact@acmecorp.com',
    naicsCode: '541511', // Custom Computer Programming Services
    dbaName: 'Acme Tech',
    website: 'https://www.acmecorp.com'
);

$organizationRequest = new CreateOrganizationCustomerRequest(
    organizationDetails: $organizationDetails
);

try {
    $orgCustomer = $client->customers()->createOrganization($organizationRequest);

    echo "Organization Customer Created!\n";
    echo "  Customer ID: {$orgCustomer->customerId}\n";
    echo "  Organization Name: {$orgCustomer->organizationName}\n";
    echo "  Type: " . ($orgCustomer->isOrganization() ? 'Organization' : 'Person') . "\n";
} catch (LsbxException $e) {
    echo "Failed to create organization customer: " . $e->getMessage() . "\n";
}

// =============================================================================
// Search for Customers
// =============================================================================

echo "\nSearching for customers...\n";

// Find by name
$customer = $client->customers()->findByName('John', 'Doe');
if ($customer) {
    echo "Found customer by name: {$customer->customerId}\n";
}

// Find by tax ID
$customer = $client->customers()->findByTaxId('123456789');
if ($customer) {
    echo "Found customer by tax ID: {$customer->customerId}\n";
}

// Find by customer ID
$customer = $client->customers()->findById('109392');
if ($customer) {
    echo "Found customer by ID: {$customer->firstName} {$customer->lastName}\n";
}

// Find organization by name
$orgCustomer = $client->customers()->findOrganizationByName('Acme Corporation');
if ($orgCustomer) {
    echo "Found organization: {$orgCustomer->organizationName}\n";
}

// =============================================================================
// Update a Customer
// =============================================================================

echo "\nUpdating customer...\n";

$updateRequest = new UpdateCustomerRequest(
    email: 'john.doe.updated@example.com',
    phone: new Phone(number: '5559999999')
);

try {
    $updatedCustomer = $client->customers()->update('109392', $updateRequest);
    echo "Customer updated successfully!\n";
    echo "  New Email: {$updatedCustomer->email}\n";
} catch (LsbxException $e) {
    echo "Failed to update customer: " . $e->getMessage() . "\n";
}

// =============================================================================
// Create Customer with Idempotency Key (Safe Retries)
// =============================================================================

echo "\nCreating customer with idempotency key...\n";

$idempotencyKey = 'unique-request-id-' . uniqid();

try {
    // This request can be safely retried with the same idempotency key
    $customer = $client->customers()->createPerson($personRequest, $idempotencyKey);
    echo "Customer created with idempotency key: {$customer->customerId}\n";

    // Retrying with the same key returns the same customer (no duplicate created)
    $sameCustomer = $client->customers()->createPerson($personRequest, $idempotencyKey);
    echo "Same customer returned on retry: {$sameCustomer->customerId}\n";
} catch (LsbxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nCustomer management examples complete!\n";
