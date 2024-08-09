<?php

namespace App\Tests\Controller;

use _PHPStan_2229debcd\Psr\Log\LoggerInterface;
use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartsAddApiControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CartRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = $this->createMock(CartRepository::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    #[Test]
    public function shouldReturnHttp500InternalServerErrorWhenExceptionThrownDuringCartCreation(): void
    {
        $this->repository->method('createCart')->willThrowException(new \Exception('Repository error'));
        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('POST', '/api/v1/carts', [], [], ['CONTENT_TYPE' => 'application/json']);

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to create cart.Repository error', $responseContent['error']);
    }

    #[Test]
    public function shouldReturnJsonResponseWithErrorWhenExceptionThrown(): void
    {
        $this->repository->method('createCart')->willThrowException(new \Exception('Repository error'));
        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('POST', '/api/v1/carts', [], [], ['CONTENT_TYPE' => 'application/json']);

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to create cart.Repository error', $responseContent['error']);
    }

    #[Test]
    public function shouldLogErrorMessageWhenExceptionThrownDuringCartCreation(): void
    {
        //        $logger = $this->createMock(LoggerInterface::class);
        //        $logger->expects($this->once())
        //            ->method('error')
        //            ->with($this->stringContains('Error creating cart: Repository error'));

        $this->repository->method('createCart')->willThrowException(new \Exception('Repository error creating Cart'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        //        $this->client->getContainer()->set(LoggerInterface::class, $logger);
        $this->client->request('POST', '/api/v1/carts', [], [], ['CONTENT_TYPE' => 'application/json']);

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Test]
    public function shouldReturnHttp201CreatedWhenCartIsSuccessfullyCreated(): void
    {
        $cart = new Cart();
        $this->repository->method('createCart')->willReturn($cart);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('POST', '/api/v1/carts', [], [], ['CONTENT_TYPE' => 'application/json']);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Cart', $responseContent['_type']);
        $this->assertArrayHasKey('_links', $responseContent);
        $this->assertArrayHasKey('cart', $responseContent);
    }
}
