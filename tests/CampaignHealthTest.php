<?php

namespace App\Tests;

use App\Entity\Campaign;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use ReflectionClass;

class CampaignHealthTest extends KernelTestCase
{
    private function setId(Campaign $campaign, $id)
    {
        $reflection = new ReflectionClass($campaign);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($campaign, $id);
    }

    public function testCampaignShowTemplateWithZeroFinancialGoal(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $twig = $container->get(Environment::class);

        $campaign = new Campaign();
        $this->setId($campaign, 1);
        $campaign->setTitle('Test Campaign');
        $campaign->setCurrentAmount('100.00');
        $campaign->setFinancialGoal('0.00');

        $html = $twig->render('campaign/show.html.twig', [
            'campaign' => $campaign,
        ]);

        $this->assertStringContainsString('0%', $html);
        $this->assertStringContainsString('of $0 target objective', $html);
    }

    public function testCampaignIndexTemplateWithZeroFinancialGoal(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $twig = $container->get(Environment::class);

        $campaign = new Campaign();
        $this->setId($campaign, 1);
        $campaign->setTitle('Test Campaign');
        $campaign->setCurrentAmount('100.00');
        $campaign->setFinancialGoal('0.00');

        $html = $twig->render('campaign/index.html.twig', [
            'campaigns' => [$campaign],
        ]);

        $this->assertStringContainsString('0%', $html);
    }
}
