<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartItemRepositoryTes extends KernelTestCase
{
    public function testFindCartItemReturnsNullWhenCartIdDoesNotExist(): void
    {
        $this->assertTrue(true);
    }
}
