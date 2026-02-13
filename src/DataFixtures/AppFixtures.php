<?php

namespace App\DataFixtures;

use App\Entity\Campaign;
use App\Entity\Donor;
use App\Entity\Asset;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Create a Test Donor
        $donor = new Donor();
        $donor->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('john.doe@example.com')
            ->setPhone('+34600000000');
        $manager->persist($donor);

        // 2. Create Sample Campaigns
        $campaignData = [
            [
                'title' => 'Emergency Health Relief',
                'description' => 'Providing medical supplies to regions in need.',
                'goal' => '50000.00',
                'video' => ['url' => 'https://example.com/health.mp4', 'autoplay' => true]
            ],
            [
                'title' => 'Scholarships for All',
                'description' => 'Helping low-income students access higher education.',
                'goal' => '25000.00',
                'video' => ['url' => 'https://example.com/edu.mp4', 'autoplay' => false]
            ],
            [
                'title' => 'Reforestation Project 2026',
                'description' => 'Planting 10,000 trees in the local mountains.',
                'goal' => '10000.00',
                'video' => ['url' => 'https://example.com/trees.mp4', 'autoplay' => true]
            ]
        ];

        foreach ($campaignData as $data) {
            $campaign = new Campaign();
            $campaign->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setFinancialGoal($data['goal'])
                ->setVideoConfig($data['video']);
            
            $manager->persist($campaign);

            // Add a placeholder asset for each campaign
            $asset = new Asset();
            $asset->setFilename('header_image.jpg')
                ->setMimeType('image/jpeg')
                ->setFilePath('/uploads/campaigns/placeholder.jpg')
                ->setCampaign($campaign);
            
            $manager->persist($asset);
        }

        $manager->flush();
    }
}
