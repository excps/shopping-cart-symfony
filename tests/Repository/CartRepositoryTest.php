<?php

namespace App\Tests\Repository;

use App\Entity\Cart;
use App\Entity\CartItem;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    public array $cart_ids = [];

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    #[Test]
    public function createCart(): void
    {
        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->assertNotNull($cart);
        $this->cart_ids[] = $cart->getId();

        $db_cart = $this->entityManager->getRepository(Cart::class)->findById($cart->getId());
        $this->assertNotNull($db_cart);

        if (null !== $db_cart) {
            $this->assertEquals($cart->getCode(), $db_cart->getCode());
            $this->assertEquals($cart->getCreatedAt()->getTimezone()->getName(), $db_cart->getCreatedAt()->getTimezone()->getName());
            $this->assertEquals($cart->getTotalPrice(), $db_cart->getTotalPrice());
        }

        $this->cartCleanup();
    }

    //    #[Test]
    //    public function searchByName(): void
    //    {
    //        $cart = $this->entityManager
    //            ->getRepository(Cart::class)
    //            ->findOneBy(['id' => 1]);
    //
    //        $this->assertNull($cart);
    //    }

    #[Test]
    public function MyFindAll(): void
    {
        $carts = $this->entityManager
            ->getRepository(Cart::class)
            ->myFindAll();

        $this->assertEmpty($carts);
        $this->assertEquals(0, count($carts));

        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();
        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();

        $db_carts = $this->entityManager
            ->getRepository(Cart::class)
            ->myFindAll();
        $this->assertEquals(2, count($db_carts));

        $this->cartCleanup();
    }

    #[Test]
    public function shouldSuccessfullySaveCart(): void
    {
        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();
        $time = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $cart->setCreatedAt($time);
        $updated = $this->entityManager->getRepository(Cart::class)->save($cart);

        $this->assertEquals($time, $updated->getCreatedAt());

        $this->cartCleanup();
    }

    #[Test]
    public function shouldAddCartItemToEmptyCartAndVerifyTotalPrice(): void
    {
        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();
        $item = new CartItem();
        $item->setCode('TEST-ITEM');
        $item->setName('Test Item');
        $item->setPrice(100);
        $item->setQuantity(2);
        $item->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        $cart = $this->entityManager->getRepository(Cart::class)->addItem($cart, $item);

        $this->assertCount(1, $cart->getCartItems());
        $this->assertEquals(200, $cart->getTotalPrice());

        $this->cartCleanup();
    }

    #[Test]
    public function shouldReturnNullWhenCartItemDoesNotExistInSpecifiedCart(): void
    {
        $cartItemRepository = $this->entityManager->getRepository(CartItem::class);
        $result = $cartItemRepository->findCartItem(99999, 99999); // Assuming 999 is a non-existent item ID and 1 is a valid cart ID

        $this->assertNull($result);
    }

    #[Test]
    public function shouldPersistCartItemEntityToDatabaseWhenSaveIsCalled(): void
    {
        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();

        $cartItem = new CartItem();
        $cartItem->setCode('TEST-ITEM');
        $cartItem->setName('Test Item');
        $cartItem->setPrice(100);
        $cartItem->setQuantity(2);
        $cartItem->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        $cartItemRepository = $this->entityManager->getRepository(CartItem::class);
        $cartItemRepository->save($cartItem);

        $savedCartItem = $cartItemRepository->find($cartItem->getId());
        $this->assertNotNull($savedCartItem);
        $this->assertEquals($cartItem->getCode(), $savedCartItem->getCode());
        $this->assertEquals($cartItem->getName(), $savedCartItem->getName());
        $this->assertEquals($cartItem->getPrice(), $savedCartItem->getPrice());
        $this->assertEquals($cartItem->getQuantity(), $savedCartItem->getQuantity());

        $this->cartCleanup();
    }

    #[Test]
    public function shouldDeleteCartItem(): void
    {
        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();
        $item = new CartItem();
        $item->setCode('TEST-ITEM');
        $item->setName('Test Item');
        $item->setPrice(100);
        $item->setQuantity(2);
        $item->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        $checked = $this->entityManager->getRepository(Cart::class)->addItem($cart, $item);

        $this->assertCount(1, $checked->getCartItems());
        $this->assertEquals(200, $checked->getTotalPrice());

        $cart_after_delete_item = $this->entityManager->getRepository(Cart::class)->deleteItem($cart, $item);
        $this->assertCount(0, $cart_after_delete_item->getCartItems());
        $this->assertEquals(0, $checked->getTotalPrice());

        $this->cartCleanup();
    }

    #[Test]
    public function shouldRemoveCart(): void
    {
        $carts = $this->entityManager->getRepository(Cart::class)->myFindAll();
        $this->assertEquals(0, count($carts));

        $cart = $this->entityManager->getRepository(Cart::class)->createCart();
        $this->cart_ids[] = $cart->getId();
        $carts_new = $this->entityManager->getRepository(Cart::class)->myFindAll();
        $this->assertEquals(1, count($carts_new));

        $this->entityManager->getRepository(Cart::class)->removeCart($cart);
        $carts_after_delete = $this->entityManager->getRepository(Cart::class)->myFindAll();
        $this->assertEquals(0, count($carts_after_delete));

        $this->cartCleanup();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function cartCleanup(): void
    {
        foreach ($this->cart_ids as $id) {
            $cart = $this->entityManager->getRepository(Cart::class)->findById($id);
            if ($cart) {
                $this->entityManager->remove($cart);
                $this->entityManager->flush();
            }
        }
        $this->cart_ids = [];
    }
}
