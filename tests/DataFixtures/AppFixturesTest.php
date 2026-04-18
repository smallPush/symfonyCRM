<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use App\Entity\Donor;
use App\Entity\Campaign;
use App\Entity\Asset;

class AppFixturesTest extends TestCase
{
    public function testLoadFixtures(): void
    {
        $fixture = new AppFixtures();

        // Use createMock to create a mock of the ObjectManager interface
        $manager = $this->createMock(ObjectManager::class);

        // We expect persist to be called 7 times (1 Donor + 3 Campaigns + 3 Assets)
        $manager->expects($this->exactly(7))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                // Verify that only Donor, Campaign, or Asset entities are persisted
                $this->assertTrue(
                    $entity instanceof Donor ||
                    $entity instanceof Campaign ||
                    $entity instanceof Asset,
                    'Persisted entity is not a Donor, Campaign, or Asset.'
                );
            });

        // We expect flush to be called 2 times (once in loadCampaigns, once in load)
        $manager->expects($this->exactly(2))
            ->method('flush');

        // We expect clear to be called 1 time (in loadCampaigns)
        $manager->expects($this->exactly(1))
            ->method('clear');

        // Call the load method to trigger the fixtures
        $fixture->load($manager);
    }
}
