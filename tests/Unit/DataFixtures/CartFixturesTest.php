<?php

namespace App\Tests\Unit\DataFixtures;

use App\DataFixtures\CartFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class CartFixturesTest extends TestCase
{
    public function testLoadCallsFlushOnce(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('flush');

        $fixtures = new CartFixtures();
        $fixtures->load($manager);
    }

    public function testLoadDoesNotPersistAnyCartObjectWhenCommentedOut(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->never())
            ->method('persist');

        $fixtures = new CartFixtures();
        $fixtures->load($manager);
    }
}
