<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartsDeleteApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CartRepository $repository;

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

    public function testShouldReturnJsonErrorWhenExceptionThrownFetchingCart(): void
    {
        $this->repository->method('findById')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('GET', '/api/v1/carts/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to get cart.', $responseContent['error']);
    }

    public function testShouldReturnHttpInternalServerErrorWhenExceptionThrownFetchingCartById(): void
    {
        $this->repository->method('findById')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('GET', '/api/v1/carts/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to get cart.', $responseContent['error']);
    }

    public function testShouldReturnHttpInternalServerErrorWhenRepositoryThrowsExceptionWhileFindingCartById(): void
    {
        $this->repository->method('findById')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('DELETE', '/api/v1/carts/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to delete cart.', $responseContent['error']);
    }

    public function testShouldReturnHttpInternalServerErrorWhenRepositoryThrowsExceptionWhileRemovingCart(): void
    {
        $this->repository->method('findById')->willReturn(new Cart());
        $this->repository->method('removeCart')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('DELETE', '/api/v1/carts/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to delete cart.', $responseContent['error']);
    }

    public function testShouldHandleNonIntegerCartIdGracefullyAndReturnHttpNotFound(): void
    {
        $this->client->request('GET', '/api/v1/carts/999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(null, $responseContent['_type']);
        $this->assertArrayHasKey('_links', $responseContent);
        $links = $responseContent['_links'];
        $selfLink = array_filter($links, function ($link) {
            return 'carts' === $link['rel'] && 'GET' === $link['method'];
        });
        $this->assertNotEmpty($selfLink);
    }

    public function testShouldReturnHttpBadRequestWhenRequestDataContainsInvalidDataTypes(): void
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
                'name' => 456, // Invalid data type, should be string
                'price' => 'invalid', // Invalid data type, should be integer
                'quantity' => 'invalid', // Invalid data type, should be integer
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid request data: Missing or invalid item code. Missing or invalid item name. Missing or invalid item price. Missing or invalid item quantity. ', $responseContent['error']);
    }

    public function testShouldReturnHttpNotFoundWhenAddingItemToNonExistentCart(): void
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

    public function testShouldReturnHttpNotFoundWhenTryingToDeleteNonExistentCart(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('DELETE', '/api/v1/carts/999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($this->client->getResponse()->getContent());
    }

    public function testShouldReturnHttpOkAndCorrectCartWhenValidCartIdProvided(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $this->repository->method('findById')->willReturn($cart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('GET', '/api/v1/carts/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, $responseContent['cart']['id']);
        $selfLink = array_filter($responseContent['_links'], function ($link) {
            return 'self' === $link['rel'] && 'GET' === $link['method'];
        });
        $this->assertNotEmpty($selfLink);
    }
}
