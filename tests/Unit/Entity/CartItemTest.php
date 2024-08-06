<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use PHPUnit\Framework\TestCase;

class CartItemTest extends TestCase
{
    public function testGetIdReturnsNullForNewCartItem(): void
    {
        $cartItem = new CartItem();
        $this->assertNull($cartItem->getId());
        $this->assertNull($cartItem->getCode());
        $this->assertNull($cartItem->getCart());
        $this->assertNull($cartItem->getName());
        $this->assertNull($cartItem->getPrice());
        $this->assertNull($cartItem->getQuantity());
    }

    public function testSetAndGet(): void
    {
        $created_at = new \DateTimeImmutable('2023-10-01 12:00:00', new \DateTimeZone('UTC'));
        $cartItem = new CartItem();
        $cartItem->setId(123);
        $this->assertEquals(123, $cartItem->getId());
        $cartItem->setCode('123-abc');
        $this->assertEquals('123-abc', $cartItem->getCode());
        $cartItem->setName('Test Item');
        $this->assertEquals('Test Item', $cartItem->getName());
        $cartItem->setPrice(199);
        $this->assertEquals(199, $cartItem->getPrice());
        $cartItem->setQuantity(5);
        $this->assertEquals(5, $cartItem->getQuantity());
        $this->assertNull($cartItem->getCreatedAt());
        $cartItem->setCreatedAt($created_at);
        $this->assertEquals($created_at, $cartItem->getCreatedAt());
        $cart = new Cart();
        $cartItem->setCart($cart);
        $this->assertEquals($cart, $cartItem->getCart());
    }
}
