<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Common;

class CddQuestion
{
    public function __construct(
        public readonly string $id,
        public readonly string $answerId,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'answer' => [
                'id' => $this->answerId,
            ],
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            answerId: $data['answer']['id'] ?? '',
        );
    }

    /**
     * Create a "Not a PEP" response (most common case)
     */
    public static function notPoliticallyExposed(): self
    {
        return new self(id: '1', answerId: '2');
    }

    /**
     * Create a "Is a PEP" response
     */
    public static function isPoliticallyExposed(): self
    {
        return new self(id: '1', answerId: '1');
    }
}
