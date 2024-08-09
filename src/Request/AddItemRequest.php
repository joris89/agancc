<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class AddItemRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly mixed $product_id,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public readonly mixed $quantity
    ) {
    }
}
