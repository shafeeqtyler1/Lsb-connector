<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Customer;

use ShafeeqKt\LsbConnector\DTO\Common\PersonDetails;
use ShafeeqKt\LsbConnector\DTO\Common\OrganizationDetails;

class UpdateCustomerRequest
{
    public function __construct(
        public readonly string $type,
        public readonly ?PersonDetails $personDetails = null,
        public readonly ?OrganizationDetails $organizationDetails = null,
    ) {}

    public function toArray(): array
    {
        $data = ['type' => $this->type];

        if ($this->personDetails !== null) {
            $data['person_details'] = $this->personDetails->toArray();
        }

        if ($this->organizationDetails !== null) {
            $data['organization_details'] = $this->organizationDetails->toArray();
        }

        return $data;
    }

    public static function forPerson(PersonDetails $personDetails): self
    {
        return new self(type: 'PERSON', personDetails: $personDetails);
    }

    public static function forOrganization(OrganizationDetails $organizationDetails): self
    {
        return new self(type: 'ORGANIZATION', organizationDetails: $organizationDetails);
    }
}
