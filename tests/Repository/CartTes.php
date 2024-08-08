<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Cart;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartTes extends KernelTestCase
{
    private EntityManager $em;

    protected function setUp(): void
    {
        self::bootKernel();
        /* @var $this->em \Doctrine\ORM\EntityManagerInterface */
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        $this->em->close();
        // Reset any custom exception handlers here
        restore_exception_handler();
        parent::tearDown();
    }

    public function testCart(): void
    {
        $carts = $this->em->getRepository(Cart::class)->findAll();
        self::assertNotNull($carts);
    }

    public function testCartRepository(): void
    {
        $cart = new Cart();
        $cart->setCode('TestCart');
        $cart->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $cart->getTotalPrice();

        $this->em->persist($cart);
        $this->em->flush();

        $new_cart = $this->em->getRepository(Cart::class)->findOneBy([
            'code' => 'TestCart',
        ]);

        self::assertNotNull($new_cart);
        self::assertEquals('TestCart', $new_cart->getCode());
        self::assertEquals(0, $new_cart->getTotalPrice());
        $id = $new_cart->getId();

        //        $cart_by_id = $this->em->getRepository(Cart::class)->findById($id);
        //        self::assertEquals('TestCart', $cart_by_id->getCode());
        //        self::assertEquals($id, $cart_by_id->getId());
        //
        //        $this->em->getRepository(Cart::class)->removeCart($cart_by_id);
        //
        //        $bullCart = $this->em->getRepository(Cart::class)->findById($id);
        //        self::assertNull($bullCart);
    }
}
