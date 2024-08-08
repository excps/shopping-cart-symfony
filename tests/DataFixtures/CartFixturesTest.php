<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\CartFixtures;
use App\Entity\Cart;
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

    public function testLoadCallsPersistAny(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('flush');

        $fixtures = new CartFixtures();
        $this->expectNotToPerformAssertions();
        $fixtures->load($manager);
    }

    public function testLoadDoesNotPersistAnyCartObjectWhenCommentedOut(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('persist');

        $fixtures = new CartFixtures();
        $this->expectNotToPerformAssertions();
        $fixtures->load($manager);
    }

    public function testCartIsPersistedWithCorrectCode(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Cart $cart) {
                return 'TestCartONE' === $cart->getCode();
            }));

        $fixtures = new CartFixtures();
        $fixtures->load($manager);
    }

    public function testCartIsPersistedWithCorrectCreationDateInUTC(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Cart $cart) {
                $expectedDate = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

                return $cart->getCreatedAt()->format('Y-m-d H:i:s') === $expectedDate->format('Y-m-d H:i:s')
                       && 'UTC' === $cart->getCreatedAt()->getTimezone()->getName();
            }));

        $fixtures = new CartFixtures();
        $fixtures->load($manager);
    }
}
