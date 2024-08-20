<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartItemDeleteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CartRepository $repository;
    private CartItemRepository $item_repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = $this->createMock(CartRepository::class);
        $this->item_repository = $this->createMock(CartItemRepository::class);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    #[Test]
    public function shouldReturnHttpInternalServerErrorWhenRepositoryThrowsExceptionWhileRemovingCart(): void
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

    #[Test]
    public function shouldReturnHttpOkWhenCartItemIsSuccessfullyDeleted(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $cartItem = new CartItem();
        $cartItem->setId(1);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cartItem);
        $this->repository->method('deleteItem')->willReturn($cart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);
        $this->client->request('DELETE', '/api/v1/carts/1/items/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Cart', $responseContent['_type']);
        $selfLink = array_filter($responseContent['_links'], function ($link) {
            return 'self' === $link['rel'] && 'DELETE' === $link['method'];
        });
        $this->assertNotEmpty($selfLink);
    }

    #[Test]
    public function shouldReturnHttpNotFoundWhenCartIsNotFound(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('DELETE', '/api/v1/carts/1/items/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function shouldVerifyResponseContainsCorrectLinksForRelatedActions(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $cartItem = new CartItem();
        $cartItem->setId(1);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cartItem);
        $this->repository->method('deleteItem')->willReturn($cart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);
        $this->client->request('DELETE', '/api/v1/carts/1/items/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $expectedLinks = [
            [
                'href' => 'http://localhost/api/v1/carts/1/items/1',
                'method' => 'DELETE',
                'rel' => 'self',
                'title' => 'Delete Item',
            ],
            [
                'href' => 'http://localhost/api/v1/carts/1/items',
                'method' => 'POST',
                'rel' => 'Item',
                'title' => 'Add Item',
            ],
            [
                'href' => 'http://localhost/api/v1/carts/1',
                'method' => 'GET',
                'rel' => 'cart',
                'title' => 'Show Cart',
            ],
            [
                'href' => 'http://localhost/api/v1/carts/1',
                'method' => 'DELETE',
                'rel' => 'delete',
                'title' => 'Delete Cart',
            ],
            [
                'href' => 'http://localhost/api/v1/carts',
                'method' => 'GET',
                'rel' => 'carts',
                'title' => 'All Carts',
            ],
        ];

        $this->assertEquals($expectedLinks, $responseContent['_links']);
    }

    #[Test]
    public function shouldReturnHttpInternalServerErrorWhenItemRepositoryThrowsExceptionWhileFindingCartItem(): void
    {
        $cart = new Cart();
        $cart->setId(1);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);
        $this->client->request('DELETE', '/api/v1/carts/1/items/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to delete cart item.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpInternalServerErrorWhenItemRepositoryThrowsExceptionWhileDeletingCartItem(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $cartItem = new CartItem();
        $cartItem->setId(1);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cartItem);
        $this->repository->method('deleteItem')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);
        $this->client->request('DELETE', '/api/v1/carts/1/items/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to delete cart item.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnHttpInternalServerErrorWhenRequestSchemeIsInvalid(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $cartItem = new CartItem();
        $cartItem->setId(1);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cartItem);
        $this->repository->method('deleteItem')->willReturn($cart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);

        $request = $this->client->request('DELETE', '/api/v1/carts/1/items/1', [], [], [
            'HTTP_HOST' => 'invalid_scheme://localhost',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function shouldVerifyResponseContainsCorrectCartDataAfterItemDeletion(): void
    {
        $cart = new Cart();
        $cart->setId(1);
        $cartItem = new CartItem();
        $cartItem->setId(1);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cartItem);
        $this->repository->method('deleteItem')->willReturn($cart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);
        $this->client->request('DELETE', '/api/v1/carts/1/items/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Cart', $responseContent['_type']);
        $this->assertEquals(1, $responseContent['cart']['id']);
        $selfLink = array_filter($responseContent['_links'], function ($link) {
            return 'self' === $link['rel'] && 'DELETE' === $link['method'];
        });
        $this->assertNotEmpty($selfLink);
    }
}
