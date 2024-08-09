<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartsListApiControllerTest extends WebTestCase
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

    #[Test]
    public function shouldReturnEmptyListWhenRepositoryHasNoCarts(): void
    {
        $this->repository->method('findAll')->willReturn([]);

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('GET', '/api/v1/carts');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([], $responseContent['items']);
        $selfLink = array_filter($responseContent['_links'], function ($link) {
            return 'self' === $link['rel'] && 'GET' === $link['method'];
        });
        $this->assertNotEmpty($selfLink);
    }

    #[Test]
    public function shouldReturnHttpInternalServerErrorWhenRepositoryThrowsException(): void
    {
        $this->repository->method('findAll')->willThrowException(new \Exception('Repository error'));

        $this->client->getContainer()->set(CartRepository::class, $this->repository);
        $this->client->request('GET', '/api/v1/carts');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Failed to get carts', $responseContent['error']);
    }
}
