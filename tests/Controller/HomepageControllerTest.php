<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        // Reset any custom exception handlers here
        restore_exception_handler();
        parent::tearDown();
    }

    public function testHomepageReturnsHelloCart(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Hello, Cart!');
    }

    public function testHomepageReturns200StatusCode(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testIndexFailWrongMethod(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class);
        $client->request('POST', '/');
    }
}
