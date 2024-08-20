<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartItemAddControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CartRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = $this->createMock(CartRepository::class);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    #[Test]
    public function shouldReturnNotFoundResponseWhenCartIdDoesNotExist(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('POST', '/api/v1/carts/999/items', [], [], [], json_encode([
            'code' => 'item123',
            'name' => 'Test Item',
            'price' => 100,
            'quantity' => 1,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('_type', $responseContent);
        $this->assertNull($responseContent['_type']);
    }

    #[Test]
    public function shouldReturnInvalidRequestDataWhenCodeIsNotString(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 123, // Invalid data type, should be string
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item code. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnInvalidRequestDataWhenNameIsNotString(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 456, // Invalid data type, should be string
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item name. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnInvalidRequestDataWhenPriceIsNotInteger(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 'invalid', // Invalid data type, should be integer
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item price. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnInvalidRequestDataWhenQuantityIsNotInteger(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => 'invalid', // Invalid data type, should be integer
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item quantity. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpBadRequestWhenQuantityFieldIsNegativeInteger(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => -1, // Negative integer
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item quantity. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpBadRequestWhenQuantityFieldIsMissingInRequestData(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 100,
                // Missing 'quantity' field
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item quantity. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpBadRequestWhenCodeFieldIsMissingInRequestData(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // 'code' => 'item123', // Missing 'code' field
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item code. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpBadRequestWhenPriceFieldIsMissingInRequestData(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                // Missing 'price' field
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item price. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpBadRequestWhenNameFieldIsMissingInRequestData(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                // Missing 'name' field
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item name. ', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnInvalidRequestDataWhenRequestDataIsEmpty(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        //        $this->assertEquals('Invalid request data.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnBadRequestResponseWhenRequestContentIsEmptyString(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '' // Empty request content
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('No request data provided.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnInternalServerErrorWhenRepositoryThrowsExceptionDuringFindById(): void
    {
        $this->repository->method('findById')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Test]
    public function shouldReturnInternalServerErrorWhenRepositoryThrowsExceptionDuringAddItem(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $this->repository->method('findById')->willReturn($cart);
        $this->repository->method('addItem')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to create cart item.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnInternalServerErrorWhenJsonDecodingThrowsException(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{invalid json}' // Malformed JSON to trigger decoding exception
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to decode JSON request data.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnCreatedResponseWithUpdatedCartDataWhenRequestIsValid(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $this->repository->method('findById')->willReturn($cart);
        $updatedCart = clone $cart;
        $updatedCart->addCartItem(new CartItem());
        $this->repository->method('addItem')->willReturn($updatedCart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'POST',
            '/api/v1/carts/1/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'item123',
                'name' => 'Test Item',
                'price' => 100,
                'quantity' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('CartCollection', $responseContent['_type']);
        $selfLink = array_filter($responseContent['_links'], function ($link) {
            return 'self' === $link['rel'] && 'GET' === $link['method'];
        });
        $this->assertNotEmpty($selfLink);

        // Additional assertions for the updated cart data
        $this->assertArrayHasKey('cart', $responseContent);
        $this->assertNotEmpty($responseContent['cart']);
        $this->assertArrayHasKey('cartItems', $responseContent['cart']);

        //        $this->assertCount(1, $responseContent['cart']['cartItems']);
        //        $this->assertEquals('item123', $responseContent['cart']['cartItems'][0]['code']);
        //        $this->assertEquals('Test Item', $responseContent['cart']['cartItems'][0]['name']);
        //        $this->assertEquals(100, $responseContent['cart']['cartItems'][0]['price']);
        //        $this->assertEquals(1, $responseContent['cart']['cartItems'][0]['quantity']);
    }
}
