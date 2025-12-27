<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Account;

class UpdateAccountRequest
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $description = null,
    ) {}

    public function toArray(): array
    {
        $accountDetails = [];

        if ($this->description !== null) {
            $accountDetails['description'] = $this->description;
        }

        return [
            'type' => $this->type,
            'account_details' => $accountDetails,
        ];
    }
}
