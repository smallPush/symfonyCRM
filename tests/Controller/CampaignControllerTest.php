<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CampaignControllerTest extends WebTestCase
{
    public function testIndexCacheHeaders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/campaign/');

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());

        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('s-maxage=3600', $cacheControl);
    }
}
