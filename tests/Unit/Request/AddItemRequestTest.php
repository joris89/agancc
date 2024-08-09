<?php

namespace App\Tests\Unit\Request;

use App\Request\AddItemRequest;
use Error;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ReflectionClass;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddItemRequestTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testProperties(): void
    {
        $productId = 'c156cb11-2843-405d-b484-f17527aeca41';
        $quantity = 1;

        $request = new AddItemRequest($productId, $quantity);
        $errors = $this->validator->validate($request);

        $this->assertCount(0, $errors);
        $this->assertEquals($productId, $request->product_id);
        $this->assertEquals($quantity, $request->quantity);
    }

    public function testInvalidRequestBlankProductId(): void
    {
        $request = new AddItemRequest(null, 2);
        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors);
        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
    }

    public function testInvalidRequestBlankQuantity(): void
    {
        $request = new AddItemRequest('c156cb11-2843-405d-b484-f17527aeca41', null);
        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors);
        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
    }

    public function testInvalidRequestNonUuidProductId(): void
    {
        $request = new AddItemRequest('no_valid_uuid', 2);
        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors);
        $this->assertEquals('This is not a valid UUID.', $errors[0]->getMessage());
    }

    public function testInvalidRequestNonIntegerQuantity(): void
    {
        $request = new AddItemRequest('c156cb11-2843-405d-b484-f17527aeca41', 'test_string');
        $errors = $this->validator->validate($request);

        $this->assertCount(1, $errors);
        $this->assertEquals('This value should be of type integer.', $errors[0]->getMessage());
    }

    public function testAddItemRequestQuantityIsReadonly(): void
    {
        $this->expectException(Error::class);

        $request = new AddItemRequest('c156cb11-2843-405d-b484-f17527aeca41', 1);

        $this->attemptToModifyReadonlyProperty($request, 'quantity', 12);
    }

    public function testAddItemRequestProductIdIsReadonly(): void
    {
        $this->expectException(Error::class);

        $request = new AddItemRequest('c156cb11-2843-405d-b484-f17527aeca41', 1);

        $this->attemptToModifyReadonlyProperty($request, 'product_id', 'c156cb11-2843-405d-b484-f17527aeca39');
    }

    private function attemptToModifyReadonlyProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setValue($object, $value);
    }
}
