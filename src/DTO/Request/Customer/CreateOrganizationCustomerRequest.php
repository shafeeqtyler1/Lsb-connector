<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Customer;

use ShafeeqKt\LsbConnector\DTO\Common\OrganizationDetails;

class CreateOrganizationCustomerRequest
{
    public function __construct(
        public readonly OrganizationDetails $organizationDetails,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => 'ORGANIZATION',
            'organization_details' => $this->organizationDetails->toArray(),
        ];
    }
}
