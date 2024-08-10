<?php

namespace App\Tests\Controller;

use App\Controller\ControllerTrait;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTraitTest extends WebTestCase
{
    use ControllerTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    #[Test]
    public function self(): void
    {
        // todo Replace with actual implementation
        $this->assertTrue(true);
    }
}
