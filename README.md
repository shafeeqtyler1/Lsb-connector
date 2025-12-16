# LSBX Connector PHP SDK

A framework-agnostic PHP SDK for the LSBX Banking API.

## Requirements

- PHP 8.0+
- Guzzle HTTP Client 7.0+

## Installation

```bash
composer require shafeeq/lsb-connector
```

## Quick Start

```php
use Shafeeq\LsbConnector\LsbxClient;

// Initialize the client (sandbox)
$client = new LsbxClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    sandbox: true
);

// Or use static factory methods
$client = LsbxClient::sandbox('your_client_id', 'your_client_secret');
$client = LsbxClient::production('your_client_id', 'your_client_secret');
```

## Configuration Options

```php
$client = new LsbxClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    sandbox: true,
    options: [
        'timeout' => 300,              // Request timeout in seconds
        'cache_dir' => '/tmp/lsbx',    // Custom cache directory
        'logger' => function($data) {  // Custom logger
            error_log(json_encode($data));
        }
    ]
);
```

## Usage Examples

### Customers

#### Create a Person Customer

```php
use Shafeeq\LsbConnector\DTO\Request\Customer\CreatePersonCustomerRequest;
use Shafeeq\LsbConnector\DTO\Common\PersonDetails;
use Shafeeq\LsbConnector\DTO\Common\Address;
use Shafeeq\LsbConnector\DTO\Common\Phone;
use Shafeeq\LsbConnector\DTO\Common\Identification;
use Shafeeq\LsbConnector\DTO\Common\CddQuestion;

$personDetails = new PersonDetails(
    firstName: 'John',
    lastName: 'Doe',
    birthDate: '1990-01-15',
    address: new Address(
        street: '123 Main St',
        city: 'New York',
        state: 'NY',
        postalCode: '10001',
        country: 'USA'
    ),
    phone: new Phone(number: '5551234567', countryCode: 'USA'),
    identification: new Identification(
        type: Identification::TYPE_DRIVERS_LICENSE,
        number: '12345678',
        issueDate: '2020-01-01',
        expireDate: '2028-01-01',
        countryCode: 'USA'
    ),
    taxId: '123456789',
    email: 'john.doe@example.com',
    occupationCode: '15-0000'
);

$request = new CreatePersonCustomerRequest(
    personDetails: $personDetails,
    cddQuestions: [CddQuestion::notPoliticallyExposed()]
);

$response = $client->customers()->createPerson($request);
echo "Customer ID: " . $response->customerId;
```

#### Create an Organization Customer

```php
use Shafeeq\LsbConnector\DTO\Request\Customer\CreateOrganizationCustomerRequest;
use Shafeeq\LsbConnector\DTO\Common\OrganizationDetails;

$orgDetails = new OrganizationDetails(
    name: 'Acme Corp LLC',
    formationDate: '2020-01-15',
    address: new Address(
        street: '456 Business Ave',
        city: 'Los Angeles',
        state: 'CA',
        postalCode: '90001'
    ),
    phone: new Phone(number: '5559876543'),
    taxId: '987654321',
    email: 'contact@acmecorp.com',
    naicsCode: '541511',
    dbaName: 'Acme'
);

$request = new CreateOrganizationCustomerRequest(organizationDetails: $orgDetails);
$response = $client->customers()->createOrganization($request);
```

#### Search Customers

```php
// Search by name
$customer = $client->customers()->findByName('John', 'Doe');

// Search by tax ID
$customer = $client->customers()->findByTaxId('123456789');

// Search by customer ID
$customer = $client->customers()->findById('109392');

// Search organization by name
$customer = $client->customers()->findOrganizationByName('Acme Corp');
```

### Accounts

#### Create an Account

```php
use Shafeeq\LsbConnector\DTO\Request\Account\CreateAccountRequest;

$request = new CreateAccountRequest(
    type: CreateAccountRequest::TYPE_PERSON,
    customerId: '109392',
    productType: CreateAccountRequest::PRODUCT_TYPE_CHECKING,
    productCode: 'FREE',
    ownershipType: CreateAccountRequest::OWNERSHIP_SINGLE,
    description: 'Main checking account'
);

$account = $client->accounts()->create($request);
echo "Account ID: " . $account->id;

// Or use convenience methods
$checkingAccount = $client->accounts()->create(
    CreateAccountRequest::checking($customerId)
);
```

#### Get Account Information

```php
// Get account metadata
$account = $client->accounts()->get($accountId);
echo "Balance: " . $account->balance;
echo "Status: " . $account->currentAccountStatusCode;

// Get banking details (account/routing numbers)
$details = $client->accounts()->getDetails($accountId);
echo "Account Number: " . $details->accountNumber;
echo "Routing Number: " . $details->routingNumber;
```

#### Freeze/Unfreeze Account

```php
// Freeze an account
$client->accounts()->freezeAccount($accountId);

// Unfreeze an account
$client->accounts()->unfreezeAccount($accountId);
```

#### Account Limits

```php
use Shafeeq\LsbConnector\DTO\Request\Account\CreateLimitsRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\UpdateLimitsRequest;

// Create limits
$limits = $client->accounts()->createLimits($accountId, new CreateLimitsRequest(
    achDailyLimit: 10000.00,
    achPerTransactionLimit: 5000.00
));

// Get limits
$limits = $client->accounts()->getLimits($accountId);
echo "Daily Limit: " . $limits->achDailyLimit;
echo "Available: " . $limits->availableAchDailyLimit;

// Update limits
$client->accounts()->updateLimits($accountId, new UpdateLimitsRequest(
    achDailyLimit: 20000.00
));

// Delete limits
$client->accounts()->deleteLimits($accountId);
```

#### Get Transactions

```php
use Shafeeq\LsbConnector\DTO\Request\Account\GetTransactionsRequest;

// Get transactions for date range
$transactions = $client->accounts()->getTransactions(
    $accountId,
    GetTransactionsRequest::forDateRange('2024-01-01', '2024-12-31')
);

// Get last 30 days (convenience method)
$transactions = $client->accounts()->getRecentTransactions($accountId, 30);

foreach ($transactions as $tx) {
    echo "{$tx->description}: {$tx->amount}\n";
}

// Get specific transaction
$transaction = $client->accounts()->getTransaction($accountId, $transactionId);
```

### Entities (External Accounts)

#### Create an Entity

```php
use Shafeeq\LsbConnector\DTO\Request\Entity\CreateEntityRequest;

$request = new CreateEntityRequest(
    accountNumber: '123456789',
    routingNumber: '021000021',
    accountHolderName: 'John Doe',
    accountType: CreateEntityRequest::ACCOUNT_TYPE_CHECKING,
    description: 'External checking account'
);

$entity = $client->entities()->create($request);
echo "Entity ID: " . $entity->id;
```

#### List and Search Entities

```php
// List all entities
$entities = $client->entities()->list(pageNumber: 0, recordsPerPage: 20);

// Find by account and routing number
$entity = $client->entities()->findByAccountAndRouting('123456789', '021000021');
```

#### Update and Delete Entities

```php
use Shafeeq\LsbConnector\DTO\Request\Entity\UpdateEntityRequest;

// Update
$entity = $client->entities()->update($entityId, new UpdateEntityRequest(
    description: 'Updated description'
));

// Delete
$client->entities()->delete($entityId);
```

### Transfers

#### ACH Transfers

```php
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateAchTransferRequest;

// Create ACH transfer with full control
$request = new CreateAchTransferRequest(
    accountId: $accountId,
    entityId: $entityId,
    type: CreateAchTransferRequest::TYPE_DEBIT,
    amount: 100.00,
    description: 'Payment description',
    sameDayAch: true,
    externalDescription: 'Bank statement desc'
);
$transfer = $client->transfers()->createAch($request);

// Convenience methods
// Pull funds from external account (DEBIT)
$transfer = $client->transfers()->debit(
    $accountId, $entityId, 100.00, 'Pull payment', sameDayAch: true
);

// Push funds to external account (CREDIT)
$transfer = $client->transfers()->credit(
    $accountId, $entityId, 100.00, 'Payment to vendor'
);

// Get pending ACH transfers
$pending = $client->transfers()->getPendingAch($accountId);

// Cancel pending ACH transfer
$client->transfers()->cancelAchById($transferId, $accountId);
```

#### Book Transfers (Internal)

```php
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateBookTransferRequest;

// Transfer between internal accounts
$transfer = $client->transfers()->internalTransfer(
    fromAccountId: $fromAccountId,
    toAccountId: $toAccountId,
    amount: 500.00,
    description: 'Internal transfer'
);
```

#### Wire Transfers (Sandbox Only)

```php
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateWireTransferRequest;
use Shafeeq\LsbConnector\DTO\Common\Address;

$request = new CreateWireTransferRequest(
    accountId: $accountId,
    entityId: $entityId,
    amount: 10000.00,
    description: 'Wire payment',
    effectiveDate: '2024-12-15',
    recipientName: 'John Doe',
    recipientAddress: new Address(
        street: '123 Main St',
        city: 'New York',
        state: 'NY',
        postalCode: '10001'
    )
);

$transfer = $client->transfers()->createWire($request);

// Get pending wire transfers
$pending = $client->transfers()->getPendingWire($accountId);
```

### Webhooks

#### Manage Webhooks

```php
use Shafeeq\LsbConnector\DTO\Request\Webhook\CreateWebhookRequest;
use Shafeeq\LsbConnector\Resources\Webhooks;

// Create webhook
$webhook = $client->webhooks()->create(new CreateWebhookRequest(
    url: 'https://your-domain.com/webhook',
    eventScopes: [
        Webhooks::SCOPE_ACCOUNT_DEPOSIT_TRANSACTIONS,
        Webhooks::SCOPE_CUSTOMER
    ],
    signingSecret: 'your-secret-key'
));

// List webhooks
$webhooks = $client->webhooks()->list();

// Get available event scopes
$scopes = $client->webhooks()->getEventScopes();

// Delete webhook
$client->webhooks()->delete($webhookId);
```

#### Handle Webhook Events

```php
// In your webhook endpoint
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LSBX_SIGNATURE'] ?? '';

// Verify signature
if (!$client->webhooks()->verifySignature($payload, $signature, 'your-secret')) {
    http_response_code(401);
    exit('Invalid signature');
}

// Parse events (handles both single and batch)
$events = $client->webhooks()->parseEvents($payload);

foreach ($events as $event) {
    switch ($event->eventCode) {
        case 'DEPOSIT_ACCOUNT_TRANSACTION_TRANSFER':
            // Handle transfer event
            echo "Transfer on account: " . $event->accountId;
            break;
        case 'CUSTOMER':
            // Handle customer event
            echo "Customer event: " . $event->action;
            break;
    }
}
```

## Error Handling

```php
use Shafeeq\LsbConnector\Exceptions\LsbxException;
use Shafeeq\LsbConnector\Exceptions\AuthenticationException;
use Shafeeq\LsbConnector\Exceptions\ApiException;
use Shafeeq\LsbConnector\Exceptions\ValidationException;

try {
    $customer = $client->customers()->createPerson($request);
} catch (AuthenticationException $e) {
    // Handle authentication errors
    echo "Auth error: " . $e->getMessage();
} catch (ValidationException $e) {
    // Handle validation errors
    echo "Validation error: " . $e->getMessage();
    print_r($e->getErrors());
} catch (ApiException $e) {
    // Handle API errors
    echo "API error ({$e->getHttpStatusCode()}): " . $e->getMessage();
    echo "Details: " . $e->getErrorDetails();
} catch (LsbxException $e) {
    // Handle any other SDK errors
    echo "Error: " . $e->getMessage();
}
```

## Custom Cache Implementation

```php
use Shafeeq\LsbConnector\Cache\CacheInterface;

class RedisCache implements CacheInterface
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if ($ttl !== null) {
            return $this->redis->setex($key, $ttl, serialize($value));
        }
        return $this->redis->set($key, serialize($value));
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }
}

// Use custom cache
$client = new LsbxClient(
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    sandbox: true,
    options: ['cache' => new RedisCache($redis)]
);
```

## License

MIT License
