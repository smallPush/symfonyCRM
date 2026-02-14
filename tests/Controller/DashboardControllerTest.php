<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    public function testDashboardIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertSelectorTextContains('h1', 'Executive Dashboard');
    }

    public function testDashboardNoRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertFalse($client->getResponse()->isRedirect());
    }
}
