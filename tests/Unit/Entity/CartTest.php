<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testCartInitializesWithEmptyCartItems(): void
    {
        $cart = new Cart();
        $this->assertInstanceOf(ArrayCollection::class, $cart->getCartItems());
        $this->assertTrue($cart->getCartItems()->isEmpty());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $cart = new Cart();
        $createdAt = new \DateTimeImmutable('2023-10-01 12:00:00');
        $cart->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $cart->getCreatedAt());
    }

    public function testSetAndGetId(): void
    {
        $cartItem = new Cart();
        $cartItem->setId(123);
        $this->assertEquals(123, $cartItem->getId());
    }

    public function testSetAndGetCode(): void
    {
        $cartItem = new Cart();
        $cartItem->setCode('123-abc');
        $this->assertEquals('123-abc', $cartItem->getCode());
    }

    public function testAddCartItem(): void
    {
        $cart = new Cart();
        $cartItem = $this->createMock(CartItem::class);

        $cart->addCartItem($cartItem);

        $this->assertCount(1, $cart->getCartItems());
        $this->assertTrue($cart->getCartItems()->contains($cartItem));
    }

    public function testShouldNotAddDuplicateCartItems(): void
    {
        $cart = new Cart();
        $cartItem = $this->createMock(CartItem::class);

        $cart->addCartItem($cartItem);
        $cart->addCartItem($cartItem);

        $this->assertCount(1, $cart->getCartItems());
    }

    public function testRemoveCartItem(): void
    {
        $cart = new Cart();
        $cartItem = $this->createMock(CartItem::class);
        $cart->addCartItem($cartItem);
        $this->assertCount(1, $cart->getCartItems());

        $cart->removeCartItem($cartItem);
        $this->assertCount(0, $cart->getCartItems());
        $this->assertTrue(null === $cartItem->getCart());
        $this->assertFalse($cart->getCartItems()->contains($cartItem));
    }

    public function testCalculateTotalPriceWhenCartIsEmpty(): void
    {
        $cart = new Cart();
        $this->assertEquals(0, $cart->getTotalPrice());
    }

    public function testCalculateTotalPriceWithMultipleCartItems(): void
    {
        $cart = new Cart();

        $cartItem1 = $this->createMock(CartItem::class);
        $cartItem1->method('getPrice')->willReturn(100);
        $cartItem1->method('getQuantity')->willReturn(2);

        $cartItem2 = $this->createMock(CartItem::class);
        $cartItem2->method('getPrice')->willReturn(50);
        $cartItem2->method('getQuantity')->willReturn(3);

        $cart->addCartItem($cartItem1);
        $cart->addCartItem($cartItem2);

        $this->assertEquals(350, $cart->getTotalPrice());
    }
}
