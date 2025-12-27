<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\Resources;

use Shafeeq\LsbConnector\DTO\Request\Entity\CreateEntityRequest;
use Shafeeq\LsbConnector\DTO\Request\Entity\UpdateEntityRequest;
use Shafeeq\LsbConnector\DTO\Request\Entity\SearchEntityRequest;
use Shafeeq\LsbConnector\DTO\Response\Entity;

class Entities extends AbstractResource
{
    /**
     * Create a new entity (external account)
     *
     * @link https://docs.lsbxapi.com/#tag/Entities/operation/createEntity
     */
    public function create(
        CreateEntityRequest $request,
        ?string $idempotencyKey = null
    ): Entity {
        $response = $this->httpPost(
            'entities',
            $request->toArray(),
            $this->withIdempotency($idempotencyKey)
        );

        return Entity::fromArray($response->getData() ?? []);
    }

    /**
     * Get all entities
     *
     * @link https://docs.lsbxapi.com/#tag/Entities/operation/getEntities
     * @return Entity[]
     */
    public function list(int $pageNumber = 0, int $recordsPerPage = 10): array
    {
        $response = $this->httpClient->get('entities', [
            'page_number' => $pageNumber,
            'records_per_page' => $recordsPerPage,
        ]);

        $data = $response->getData() ?? [];

        // If response is a list of entities
        if (isset($data[0])) {
            return array_map(
                fn(array $item) => Entity::fromArray($item),
                $data
            );
        }

        // If response contains entities key
        if (isset($data['entities'])) {
            return array_map(
                fn(array $item) => Entity::fromArray($item),
                $data['entities']
            );
        }

        return [];
    }

    /**
     * Search for entities
     *
     * @link https://docs.lsbxapi.com/#tag/Entities/operation/searchEntities
     * @return Entity[]
     */
    public function search(
        SearchEntityRequest $request,
        int $pageNumber = 0,
        int $recordsPerPage = 10
    ): array {
        $response = $this->httpPost('entities/search', $request->toArray());

        $data = $response->getData() ?? [];

        if (isset($data[0])) {
            return array_map(
                fn(array $item) => Entity::fromArray($item),
                $data
            );
        }

        if (isset($data['entities'])) {
            return array_map(
                fn(array $item) => Entity::fromArray($item),
                $data['entities']
            );
        }

        // Single entity result
        if (isset($data['id'])) {
            return [Entity::fromArray($data)];
        }

        return [];
    }

    /**
     * Find entity by account and routing number
     */
    public function findByAccountAndRouting(string $accountNumber, string $routingNumber): ?Entity
    {
        $results = $this->search(
            SearchEntityRequest::byAccountAndRouting($accountNumber, $routingNumber)
        );

        return $results[0] ?? null;
    }

    /**
     * Update an entity
     *
     * @link https://docs.lsbxapi.com/#tag/Entities/operation/updateEntity
     */
    public function update(string $entityId, UpdateEntityRequest $request): Entity
    {
        $response = $this->httpPatch("entities/{$entityId}", $request->toArray());
        return Entity::fromArray($response->getData() ?? []);
    }

    /**
     * Delete an entity
     *
     * @link https://docs.lsbxapi.com/#tag/Entities/operation/deleteEntity
     */
    public function delete(string $entityId): bool
    {
        $response = $this->httpClient->delete("entities/{$entityId}");
        return $response->isSuccessful();
    }
}
