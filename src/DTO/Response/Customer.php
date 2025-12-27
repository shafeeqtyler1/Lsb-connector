<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Response;

use ShafeeqKt\LsbConnector\DTO\Common\PersonDetails;
use ShafeeqKt\LsbConnector\DTO\Common\OrganizationDetails;

class Customer
{
    public function __construct(
        public readonly string $customerId,
        public readonly string $type,
        public readonly ?PersonDetails $personDetails = null,
        public readonly ?OrganizationDetails $organizationDetails = null,
        public readonly array $accounts = [],
        public readonly array $rawData = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $personDetails = null;
        $organizationDetails = null;

        if (isset($data['person_details'])) {
            $personDetails = PersonDetails::fromArray($data['person_details']);
        }

        if (isset($data['organization_details'])) {
            $organizationDetails = OrganizationDetails::fromArray($data['organization_details']);
        }

        return new self(
            customerId: $data['customer_id'] ?? '',
            type: $data['type'] ?? 'PERSON',
            personDetails: $personDetails,
            organizationDetails: $organizationDetails,
            accounts: $data['accounts'] ?? [],
            rawData: $data,
        );
    }

    public function isPerson(): bool
    {
        return $this->type === 'PERSON';
    }

    public function isOrganization(): bool
    {
        return $this->type === 'ORGANIZATION';
    }
}
