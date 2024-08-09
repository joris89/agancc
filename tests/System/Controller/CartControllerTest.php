<?php

namespace App\Tests\System\Controller;

use App\DataFixtures\ProductFixtures;
use App\Entity\Product;
use App\Request\UpdateCartItemRequest;
use App\Tests\System\SystemTestCase;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use phpDocumentor\Reflection\Types\Iterable_;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CartControllerTest extends SystemTestCase
{
    protected AbstractDatabaseTool $databaseTool;

    public function testCreate(): void
    {
        $client = static::createClient();
        $client->request('POST', 'carts');
        $cart = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED, $client->getResponse());
        $this->assertNotNull($cart['id']);
        $this->assertNotNull($cart['createdAt']);
    }

    public function testView(): void
    {
        $client = static::createClient();

        $client->request('POST', 'carts');
        $cart = $this->getResponseContent($client);

        $client->request('GET', '/carts/' . $cart['id']);

        $result = $this->getResponseContent($client);

        $this->assertEquals($cart, $result);
    }

    public function testViewNoCartFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/carts/6891389f-95a1-4585-a56f-ccd9fa829124');

        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND, $client->getResponse());
        $this->assertEquals(json_encode(['error' => 'Cart not found']), $client->getResponse()->getContent());
    }

    public function testAddItemWithNoCart(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/carts/a7ac8b1d-19f0-4599-bf0c-febd5af42fdd/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(['product_id' => "a7ac8b1d-19f0-4599-bf0c-febd5af42fdd", "quantity" => 1])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND, $client->getResponse());
        $this->assertEquals(['error' => 'Cart not found'], $result);
    }

    public function testAddItemWithNoProduct(): void
    {
        $client = static::createClient();

        $client->request('POST', 'carts');
        $cart = $this->getResponseContent($client);

        $client->request(
            'POST',
            '/carts/' . $cart['id'] . '/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(['product_id' => "a7ac8b1d-19f0-4599-bf0c-febd5af42fdd", "quantity" => 1])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND, $client->getResponse());
        $this->assertEquals(['error' => 'Product not found'], $result);
    }

    public function testAddItemWithNoProductIdBlank(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/carts/a7ac8b1d-19f0-4599-bf0c-febd5af42fdd/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(["quantity" => 1])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse());

        $this->assertEquals("Validation Failed", $result["title"]);
        $this->assertEquals("product_id: This value should not be blank.", $result["detail"]);
    }

    public function testAddItemWithProductIdAsInteger(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/carts/a7ac8b1d-19f0-4599-bf0c-febd5af42fdd/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(["product_id" => 1, "quantity" => 1])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse());

        $this->assertEquals("Validation Failed", $result["title"]);
        $this->assertEquals("product_id: This is not a valid UUID.", $result["detail"]);
    }

    public function testAddItemWithQuantityBlank(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/carts/a7ac8b1d-19f0-4599-bf0c-febd5af42fdd/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(['product_id' => "a7ac8b1d-19f0-4599-bf0c-febd5af42fdd"])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse());

        $this->assertEquals("Validation Failed", $result["title"]);
        $this->assertEquals("quantity: This value should not be blank.", $result["detail"]);
    }

    public function testAddItemWithQuantityAsString(): void
    {
        $client = static::createClient();

        $requestBody = $this->getRequestBody(
            ['product_id' => "a7ac8b1d-19f0-4599-bf0c-febd5af42fdd", "quantity" => "this is a string"]
        );
        $client->request(
            'POST',
            '/carts/a7ac8b1d-19f0-4599-bf0c-febd5af42fdd/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $requestBody
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse());

        $this->assertEquals("Validation Failed", $result["title"]);
        $this->assertEquals("quantity: This value should be of type integer.", $result["detail"]);
    }

    public function testAddItem(): void
    {
        $client = static::createClient();
        $product = $this->getRandomProduct($client);

        $client->request('POST', 'carts');

        $cart = $this->getResponseContent($client);

        $client->request(
            'POST',
            'carts/' . $cart['id'] . '/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(['product_id' => $product->getId()->toString(), "quantity" => 12])
        );

        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED, $client->getResponse());
        $this->assertNotNull($result['cart']['id']);
        $this->assertNotNull($result['product']['id']);
    }

    public function testUpdateItem(): void
    {
        $client = static::createClient();
        $product = $this->getRandomProduct($client);

        $client->request('POST', 'carts');

        $cart = $this->getResponseContent($client);

        $client->request(
            'POST',
            '/carts/' . $cart['id'] . '/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(['product_id' => $product->getId()->toString(), "quantity" => 12])
        );

        $cartItem = $this->getResponseContent($client);

        $client->request(
            'PATCH',
            '/carts/' . $cart['id'] . '/items/' . $cartItem['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(["quantity" => 11])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_OK, $client->getResponse());
        $this->assertEquals(11, $result['quantity']);
    }

    public function testUpdateItemCartItemNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'PATCH',
            '/carts/6891389f-95a1-4585-a56f-ccd9fa829124/items/6891389f-95a1-4585-a56f-ccd9fa829124',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(["quantity" => 11])
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND, $client->getResponse());
        $this->assertEquals(['error' => 'CartItem not found'], $result);
    }

    public function testRemoveCartItem(): void
    {
        $client = static::createClient();
        $product = $this->getRandomProduct($client);

        $client->request('POST', 'carts');

        $cart = $this->getResponseContent($client);

        $client->request(
            'POST',
            '/carts/' . $cart['id'] . '/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->getRequestBody(['product_id' => $product->getId()->toString(), "quantity" => 12])
        );

        $cartItem = $this->getResponseContent($client);

        $client->request('DELETE', '/carts/' . $cart['id'] . '/items/' . $cartItem['id']);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NO_CONTENT, $client->getResponse());
    }

    public function testRemoveCartItemNoCartItemFound(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/carts/6891389f-95a1-4585-a56f-ccd9fa829124/items/6891389f-95a1-4585-a56f-ccd9fa829124'
        );
        $result = $this->getResponseContent($client);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_NOT_FOUND, $client->getResponse());
        $this->assertEquals(['error' => 'CartItem not found'], $result);
    }

    private function getRandomProduct(KernelBrowser $client): Product
    {
        $container = $client->getContainer();
        $databaseTool = $this->getDatabaseTool($container);
        $em = $this->getEntityManager($container);

        $databaseTool->loadFixtures([ProductFixtures::class]);

        return $em->getRepository(Product::class)->findOneBy([]);
    }
}
