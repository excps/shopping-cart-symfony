<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartItemUpdateControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CartRepository $repository;
    private CartItemRepository $item_repository;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = $this->createMock(CartRepository::class);
        $this->item_repository = $this->createMock(CartItemRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    #[Test]
    public function shouldUpdateCartItemCodeWhenCodeIsProvidedInRequestData(): void
    {
        $cart = new Cart();
        $cart_item = new CartItem();
        $cart_item->setId(1);
        $cart_item->setCode('old_code');
        $cart->addCartItem($cart_item);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cart_item);
        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);

        $this->client->request(
            'PUT',
            '/api/v1/carts/1/items/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['code' => 'new_code'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('new_code', $responseContent['cart']['cartItems'][0]['code']);
    }

    #[Test]
    public function shouldReturnJsonResponseWithUpdatedCartItemDetails(): void
    {
        $cart = new Cart();
        $cart_item = new CartItem();
        $cart_item->setId(1);
        $cart_item->setCode('old_code');
        $cart_item->setName('Old Item');
        $cart_item->setPrice(50);
        $cart_item->setQuantity(2);
        $cart->addCartItem($cart_item);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cart_item);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);

        $this->client->request(
            'PUT',
            '/api/v1/carts/1/items/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'new_code',
                'name' => 'New Item',
                'price' => 100,
                'quantity' => 5,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('new_code', $responseContent['cart']['cartItems'][0]['code']);
        $this->assertEquals('New Item', $responseContent['cart']['cartItems'][0]['name']);
        $this->assertEquals(100, $responseContent['cart']['cartItems'][0]['price']);
        $this->assertEquals(5, $responseContent['cart']['cartItems'][0]['quantity']);
    }

    #[Test]
    public function shouldReturnNotFoundResponseWhenCartIdDoesNotExist(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request(
            'PUT',
            '/api/v1/carts/999/items/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['code' => 'new_code'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Cart not found.', $responseContent['error']);
    }

    #[Test]
    public function shouldLogErrorMessageWhenExceptionThrownDuringUpdateProcess(): void
    {
        $this->repository->method('findById')->willThrowException(new \Exception('Repository error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error updating cart item: Repository error');

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(LoggerInterface::class, $this->logger);

        $this->client->request(
            'PUT',
            '/api/v1/carts/1/items/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['code' => 'new_code'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to update cart item.', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnErrorResponseWhenItemIdDoesNotExistInCart(): void
    {
        $cart = new Cart();
        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn(null);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);

        $this->client->request(
            'PUT',
            '/api/v1/carts/1/items/999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['code' => 'new_code'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Cart item not found.', $responseContent['error']);
    }

    #[Test]
    public function shouldCorrectlyUpdateCartItemDetailsWhenAllFieldsAreProvidedInRequestData(): void
    {
        $cart = new Cart();
        $cart_item = new CartItem();
        $cart_item->setId(1);
        $cart_item->setCode('old_code');
        $cart_item->setName('Old Item');
        $cart_item->setPrice(50);
        $cart_item->setQuantity(2);
        $cart->addCartItem($cart_item);

        $this->repository->method('findById')->willReturn($cart);
        $this->item_repository->method('findCartItem')->willReturn($cart_item);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->getContainer()->set(CartItemRepository::class, $this->item_repository);

        $this->client->request(
            'PUT',
            '/api/v1/carts/1/items/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'code' => 'new_code',
                'name' => 'New Item',
                'price' => 100,
                'quantity' => 5,
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('new_code', $responseContent['cart']['cartItems'][0]['code']);
        $this->assertEquals('New Item', $responseContent['cart']['cartItems'][0]['name']);
        $this->assertEquals(100, $responseContent['cart']['cartItems'][0]['price']);
        $this->assertEquals(5, $responseContent['cart']['cartItems'][0]['quantity']);
    }
}
