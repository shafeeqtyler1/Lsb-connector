<?php

declare(strict_types=1);

namespace Shafeeq\LsbConnector\DTO\Request\Account;

class FreezeAccountRequest
{
    public function __construct(
        public readonly bool $freezeAccount,
    ) {}

    public function toArray(): array
    {
        return [
            'freeze_account' => $this->freezeAccount,
        ];
    }

    public static function freeze(): self
    {
        return new self(freezeAccount: true);
    }

    public static function unfreeze(): self
    {
        return new self(freezeAccount: false);
    }
}
