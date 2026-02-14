<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class DashboardControllerTest extends WebTestCase
{
    public function testIndexRedirectsToCampaignIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/campaign/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}
