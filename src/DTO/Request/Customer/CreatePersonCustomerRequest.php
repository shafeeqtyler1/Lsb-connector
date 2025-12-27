<?php

declare(strict_types=1);

namespace ShafeeqKt\LsbConnector\DTO\Request\Customer;

use ShafeeqKt\LsbConnector\DTO\Common\PersonDetails;
use ShafeeqKt\LsbConnector\DTO\Common\CddQuestion;

class CreatePersonCustomerRequest
{
    /**
     * @param PersonDetails $personDetails Customer personal details
     * @param CddQuestion[] $cddQuestions Customer Due Diligence questions
     */
    public function __construct(
        public readonly PersonDetails $personDetails,
        public readonly array $cddQuestions = [],
    ) {}

    public function toArray(): array
    {
        $data = [
            'type' => 'PERSON',
            'person_details' => $this->personDetails->toArray(),
        ];

        if (!empty($this->cddQuestions)) {
            $data['cdd_questions'] = array_map(
                fn(CddQuestion $q) => $q->toArray(),
                $this->cddQuestions
            );
        }

        return $data;
    }
}
