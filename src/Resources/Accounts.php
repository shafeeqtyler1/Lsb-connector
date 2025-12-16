<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Resources;

use Shafeeq\LsbConnector\DTO\Request\Account\CreateAccountRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\UpdateAccountRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\FreezeAccountRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\CreateLimitsRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\UpdateLimitsRequest;
use Shafeeq\LsbConnector\DTO\Request\Account\GetTransactionsRequest;
use Shafeeq\LsbConnector\DTO\Response\CreateAccountResponse;
use Shafeeq\LsbConnector\DTO\Response\Account;
use Shafeeq\LsbConnector\DTO\Response\AccountDetails;
use Shafeeq\LsbConnector\DTO\Response\AccountLimits;
use Shafeeq\LsbConnector\DTO\Response\Transaction;

class Accounts extends AbstractResource
{
    /**
     * Create a new account
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/createAccount
     */
    public function create(
        CreateAccountRequest $request,
        ?string $idempotencyKey = null
    ): CreateAccountResponse {
        $response = $this->post(
            'accounts',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return CreateAccountResponse::fromArray($response->getData() ?? []);
    }

    /**
     * Get account information
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/getAccount
     */
    public function get(string $accountId): Account
    {
        $response = $this->httpClient->get("accounts/{$accountId}");
        return Account::fromArray($response->getData() ?? []);
    }

    /**
     * Get account banking details (account and routing numbers)
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/getAccountDetails
     */
    public function getDetails(string $accountId): AccountDetails
    {
        $response = $this->httpClient->get("accounts/{$accountId}/details");
        return AccountDetails::fromArray($response->getData() ?? []);
    }

    /**
     * Update account
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/updateAccount
     */
    public function update(string $accountId, UpdateAccountRequest $request): array
    {
        $response = $this->patch("accounts/{$accountId}", $request->toArray());
        return $response->getData() ?? [];
    }

    /**
     * Freeze or unfreeze an account
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/freezeAccount
     */
    public function freeze(string $accountId, FreezeAccountRequest $request): array
    {
        $response = $this->post("accounts/{$accountId}/freeze", $request->toArray());
        return $response->getData() ?? [];
    }

    /**
     * Freeze an account (convenience method)
     */
    public function freezeAccount(string $accountId): array
    {
        return $this->freeze($accountId, FreezeAccountRequest::freeze());
    }

    /**
     * Unfreeze an account (convenience method)
     */
    public function unfreezeAccount(string $accountId): array
    {
        return $this->freeze($accountId, FreezeAccountRequest::unfreeze());
    }

    /**
     * Get account limits
     *
     * @link https://docs.lsbxapi.com/#tag/Account-Limits/operation/getAccountLimits
     */
    public function getLimits(string $accountId): AccountLimits
    {
        $response = $this->httpClient->get("accounts/limits/{$accountId}");
        return AccountLimits::fromArray($response->getData() ?? []);
    }

    /**
     * Create account limits
     *
     * @link https://docs.lsbxapi.com/#tag/Account-Limits/operation/createAccountLimits
     */
    public function createLimits(string $accountId, CreateLimitsRequest $request): AccountLimits
    {
        $response = $this->post("accounts/limits/{$accountId}", $request->toArray());
        return AccountLimits::fromArray($response->getData() ?? []);
    }

    /**
     * Update account limits
     *
     * @link https://docs.lsbxapi.com/#tag/Account-Limits/operation/updateAccountLimits
     */
    public function updateLimits(string $accountId, UpdateLimitsRequest $request): AccountLimits
    {
        $response = $this->patch("accounts/limits/{$accountId}", $request->toArray());
        return AccountLimits::fromArray($response->getData() ?? []);
    }

    /**
     * Delete account limits
     *
     * @link https://docs.lsbxapi.com/#tag/Account-Limits/operation/deleteAccountLimits
     */
    public function deleteLimits(string $accountId): bool
    {
        $response = $this->delete("accounts/limits/{$accountId}");
        return $response->isSuccessful();
    }

    /**
     * Get account transactions
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/getAccountTransactions
     * @return Transaction[]
     */
    public function getTransactions(string $accountId, GetTransactionsRequest $request): array
    {
        $response = $this->httpClient->get(
            "accounts/transactions/{$accountId}",
            $request->toQueryParams()
        );

        $data = $response->getData() ?? [];

        return array_map(
            fn(array $item) => Transaction::fromArray($item),
            $data
        );
    }

    /**
     * Get transactions for last N days (convenience method)
     *
     * @return Transaction[]
     */
    public function getRecentTransactions(string $accountId, int $days = 30): array
    {
        return $this->getTransactions($accountId, GetTransactionsRequest::lastDays($days));
    }

    /**
     * Get a specific transaction
     *
     * @link https://docs.lsbxapi.com/#tag/Accounts/operation/getAccountTransactionbyId
     */
    public function getTransaction(string $accountId, string $transactionId): Transaction
    {
        $response = $this->httpClient->get("accounts/transactions/{$accountId}/{$transactionId}");
        return Transaction::fromArray($response->getData() ?? []);
    }
}
