<?php

namespace App\Tests\Unit\Request;

use App\Request\AddItemRequest;
use App\Request\UpdateCartItemRequest;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateCartItemRequestTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testUpdateCartItemRequestIntegerQuantity(): void
    {
        $quantity = 12;
        $request = new UpdateCartItemRequest($quantity);
        $errors = $this->validator->validate($request);

        $this->assertCount(0, $errors);
        $this->assertEquals($quantity, $request->quantity);
    }

    public function testUpdateCartItemRequestNonIntegerQuantity(): void
    {
        $request = new UpdateCartItemRequest('c156cb11-2843-405d-b484-f17527aeca41');
        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors);
        $this->assertEquals('This value should be of type integer.', $errors[0]->getMessage());
    }

    public function testUpdateCartItemRequestIsReadonly(): void
    {
        $this->expectException(Error::class);

        $request = new UpdateCartItemRequest(1);

        $this->attemptToModifyReadonlyProperty($request, 'quantity', 12);
    }

    private function attemptToModifyReadonlyProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setValue($object, $value);
    }
}
