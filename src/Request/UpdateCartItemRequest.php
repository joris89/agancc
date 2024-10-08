<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateCartItemRequest
{
    public function __construct(
        #[Assert\Type('integer')]
        public readonly mixed $quantity
    ) {
    }
}
