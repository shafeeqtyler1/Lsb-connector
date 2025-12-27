<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Resources;

use Shafeeq\LsbConnector\DTO\Request\Customer\CreatePersonCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\CreateOrganizationCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\SearchCustomerRequest;
use Shafeeq\LsbConnector\DTO\Request\Customer\UpdateCustomerRequest;
use Shafeeq\LsbConnector\DTO\Response\CreateCustomerResponse;
use Shafeeq\LsbConnector\DTO\Response\Customer;

class Customers extends AbstractResource
{
    /**
     * Create a new person customer
     *
     * @link https://docs.lsbxapi.com/#tag/Customers/operation/createCustomer
     */
    public function createPerson(
        CreatePersonCustomerRequest $request,
        ?string $idempotencyKey = null
    ): CreateCustomerResponse {
        $response = $this->httpPost(
            'customers',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return CreateCustomerResponse::fromArray($response->getData() ?? []);
    }

    /**
     * Create a new organization customer
     *
     * @link https://docs.lsbxapi.com/#tag/Customers/operation/createCustomer
     */
    public function createOrganization(
        CreateOrganizationCustomerRequest $request,
        ?string $idempotencyKey = null
    ): CreateCustomerResponse {
        $response = $this->httpPost(
            'customers',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return CreateCustomerResponse::fromArray($response->getData() ?? []);
    }

    /**
     * Search for customers
     *
     * @link https://docs.lsbxapi.com/#tag/Customers/operation/searchCustomer
     */
    public function search(SearchCustomerRequest $request): ?Customer
    {
        $response = $this->httpPost('customers/search', $request->toArray());
        $data = $response->getData();

        if (empty($data) || !isset($data['customer_id'])) {
            return null;
        }

        return Customer::fromArray($data);
    }

    /**
     * Search customer by name
     */
    public function findByName(string $firstName, string $lastName): ?Customer
    {
        return $this->search(SearchCustomerRequest::byName($firstName, $lastName));
    }

    /**
     * Search customer by tax ID
     */
    public function findByTaxId(string $taxId): ?Customer
    {
        return $this->search(SearchCustomerRequest::byTaxId($taxId));
    }

    /**
     * Search customer by customer ID
     */
    public function findById(string $customerId): ?Customer
    {
        return $this->search(SearchCustomerRequest::byCustomerId($customerId));
    }

    /**
     * Search organization by name
     */
    public function findOrganizationByName(string $organizationName): ?Customer
    {
        return $this->search(SearchCustomerRequest::byOrganizationName($organizationName));
    }

    /**
     * Update an existing customer
     *
     * @link https://docs.lsbxapi.com/#tag/Customers/operation/updateCustomer
     */
    public function update(string $customerId, UpdateCustomerRequest $request): array
    {
        $response = $this->httpPatch("customers/{$customerId}", $request->toArray());
        return $response->getData() ?? [];
    }
}
