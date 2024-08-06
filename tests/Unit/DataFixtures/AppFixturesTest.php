<?php

namespace App\Tests\Unit\DataFixtures;

use App\DataFixtures\AppFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class AppFixturesTest extends TestCase
{
    public function testLoadCallsFlushOnce(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('flush');

        $fixtures = new AppFixtures();
        $fixtures->load($manager);
    }

    public function testLoadDoesNotPersistObjectsBeforeFlush(): void
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->never())
            ->method('persist');

        $fixtures = new AppFixtures();
        $fixtures->load($manager);
    }

    public function testLoadDoesNotThrowException(): void
    {
        $manager = $this->createMock(ObjectManager::class);

        $fixtures = new AppFixtures();

        $this->expectNotToPerformAssertions();

        $fixtures->load($manager);
    }
}
