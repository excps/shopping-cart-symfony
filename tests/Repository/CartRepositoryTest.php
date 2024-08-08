<?php

namespace App\Tests\Repository;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartRepositoryTest extends KernelTestCase
{
    private MockObject&Registry $registry;
    private MockObject&LoggerInterface $logger;
    private EntityManagerInterface $manager;
    private CartRepository $repository;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(Registry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->manager = $this->createMock(EntityManager::class);

        $this->registry->method('getManagerForClass')->willReturn($this->manager);

        $this->repository = new CartRepository($this->registry, $this->logger);
    }

    protected function tearDown(): void
    {
        // Reset any custom exception handlers here
        restore_exception_handler();
        parent::tearDown();
    }

    public function testShouldLogInfoMessageWhenCreatingNewCart(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Creating new cart'));
        $this->registry->method('getManagerForClass')->willReturn($this->manager);

        $repository = new CartRepository($this->registry, $this->logger);

        $this->repository->createCart();
    }

    public function testCreateCart(): void
    {
        $cart = $this->repository->createCart();
        $cart->setCode('test-code');
        $cart->setId(9999);
        $this->manager->persist($cart);
        $this->manager->flush();

        //        $tc = $this->repository->findById(9999);
        //
        //        $this->assertEquals('test-code', $tc->getCode());

        $this->assertInstanceOf(Cart::class, $cart);
        //        $this->assertIsString($cart->getCode());
        //        $this->assertNotEmpty($cart->getCode());
        //        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->getCreatedAt());
        //        $this->assertIsNumeric($cart->getTotalPrice());
        //        $this->assertEquals(0, $cart->getTotalPrice());
    }
}
