<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Resources;

use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CancelAchTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateBookTransferRequest;
use Shafeeq\LsbConnector\DTO\Request\Transfer\CreateWireTransferRequest;
use Shafeeq\LsbConnector\DTO\Response\Transfer;

class Transfers extends AbstractResource
{
    /**
     * Create an ACH transfer
     *
     * @link https://docs.lsbxapi.com/#tag/Transfers/operation/createAchTransfer
     */
    public function createAch(
        CreateAchTransferRequest $request,
        ?string $idempotencyKey = null
    ): Transfer {
        $response = $this->httpPost(
            'transfers/ach',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return Transfer::fromArray($response->getData() ?? []);
    }

    /**
     * Create an ACH debit transfer (pull funds from external account)
     */
    public function debit(
        string $accountId,
        string $entityId,
        float $amount,
        string $description,
        bool $sameDayAch = false,
        ?string $idempotencyKey = null
    ): Transfer {
        $request = CreateAchTransferRequest::debit(
            $accountId,
            $entityId,
            $amount,
            $description,
            $sameDayAch
        );

        return $this->createAch($request, $idempotencyKey);
    }

    /**
     * Create an ACH credit transfer (push funds to external account)
     */
    public function credit(
        string $accountId,
        string $entityId,
        float $amount,
        string $description,
        bool $sameDayAch = false,
        ?string $idempotencyKey = null
    ): Transfer {
        $request = CreateAchTransferRequest::credit(
            $accountId,
            $entityId,
            $amount,
            $description,
            $sameDayAch
        );

        return $this->createAch($request, $idempotencyKey);
    }

    /**
     * Get pending ACH transfers for an account
     *
     * @link https://docs.lsbxapi.com/#tag/Transfers/operation/getPendingAch
     * @return Transfer[]
     */
    public function getPendingAch(string $accountId): array
    {
        $response = $this->httpClient->get("transfers/ach/{$accountId}");
        $data = $response->getData() ?? [];

        return array_map(
            fn(array $item) => Transfer::fromArray($item),
            $data
        );
    }

    /**
     * Cancel a pending ACH transfer
     *
     * @link https://docs.lsbxapi.com/#tag/Transfers/operation/cancelAchTransfer
     */
    public function cancelAch(
        CancelAchTransferRequest $request,
        ?string $idempotencyKey = null
    ): bool {
        $response = $this->httpDelete(
            'transfers/ach',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return $response->isSuccessful();
    }

    /**
     * Cancel a pending ACH transfer by ID (convenience method)
     */
    public function cancelAchById(
        string $transferId,
        string $accountId,
        ?string $idempotencyKey = null
    ): bool {
        return $this->cancelAch(
            new CancelAchTransferRequest($transferId, $accountId),
            $idempotencyKey
        );
    }

    /**
     * Create a book transfer (internal transfer between accounts)
     *
     * @link https://docs.lsbxapi.com/#tag/Transfers/operation/createBookTransfer
     */
    public function createBook(
        CreateBookTransferRequest $request,
        ?string $idempotencyKey = null
    ): Transfer {
        $response = $this->httpPost(
            'transfers/book',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return Transfer::fromArray($response->getData() ?? []);
    }

    /**
     * Transfer funds between internal accounts (convenience method)
     */
    public function internalTransfer(
        string $fromAccountId,
        string $toAccountId,
        float $amount,
        string $description,
        ?string $idempotencyKey = null
    ): Transfer {
        return $this->createBook(
            new CreateBookTransferRequest($fromAccountId, $toAccountId, $amount, $description),
            $idempotencyKey
        );
    }

    /**
     * Create a wire transfer (Sandbox only)
     *
     * @link https://docs.lsbxapi.com/#tag/Transfers/operation/createWireTransfer
     */
    public function createWire(
        CreateWireTransferRequest $request,
        ?string $idempotencyKey = null
    ): Transfer {
        $response = $this->httpPost(
            'transfers/wire',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return Transfer::fromArray($response->getData() ?? []);
    }

    /**
     * Get pending wire transfers for an account (Sandbox only)
     *
     * @link https://docs.lsbxapi.com/#tag/Wire-Transfers
     * @return Transfer[]
     */
    public function getPendingWire(string $accountId): array
    {
        $response = $this->httpClient->get("transfers/wire/{$accountId}");
        $data = $response->getData() ?? [];

        return array_map(
            fn(array $item) => Transfer::fromArray($item),
            $data
        );
    }
}
