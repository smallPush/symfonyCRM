<?php

namespace App\Tests\Entity;

use App\Entity\Asset;
use App\Entity\Campaign;
use App\Entity\Transaction;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CampaignTest extends TestCase
{
    public function testAddAndRemoveAsset(): void
    {
        $campaign = new Campaign();
        $asset = new Asset();

        // Initially, the collection should be empty
        $this->assertCount(0, $campaign->getAssets());

        // Add an asset
        $campaign->addAsset($asset);
        $this->assertCount(1, $campaign->getAssets());
        $this->assertTrue($campaign->getAssets()->contains($asset));
        $this->assertSame($campaign, $asset->getCampaign());

        // Add the same asset again (should not duplicate)
        $campaign->addAsset($asset);
        $this->assertCount(1, $campaign->getAssets());

        // Remove the asset
        $campaign->removeAsset($asset);
        $this->assertCount(0, $campaign->getAssets());
        $this->assertNull($asset->getCampaign());
    }

    public function testAddAndRemoveTransaction(): void
    {
        $campaign = new Campaign();
        $transaction = new Transaction();

        // Initially, the collection should be empty
        $this->assertCount(0, $campaign->getTransactions());

        // Add a transaction
        $campaign->addTransaction($transaction);
        $this->assertCount(1, $campaign->getTransactions());
        $this->assertTrue($campaign->getTransactions()->contains($transaction));
        $this->assertSame($campaign, $transaction->getCampaign());

        // Add the same transaction again (should not duplicate)
        $campaign->addTransaction($transaction);
        $this->assertCount(1, $campaign->getTransactions());

        // Remove the transaction
        $campaign->removeTransaction($transaction);
        $this->assertCount(0, $campaign->getTransactions());
        $this->assertNull($transaction->getCampaign());
    }

    public function testAddAndRemoveManager(): void
    {
        $campaign = new Campaign();
        $manager = new User();

        // Initially, the collection should be empty
        $this->assertCount(0, $campaign->getManagers());

        // Add a manager
        $campaign->addManager($manager);
        $this->assertCount(1, $campaign->getManagers());
        $this->assertTrue($campaign->getManagers()->contains($manager));
        $this->assertTrue($manager->getManagedCampaigns()->contains($campaign));

        // Add the same manager again (should not duplicate)
        $campaign->addManager($manager);
        $this->assertCount(1, $campaign->getManagers());

        // Remove the manager
        $campaign->removeManager($manager);
        $this->assertCount(0, $campaign->getManagers());
        $this->assertFalse($manager->getManagedCampaigns()->contains($campaign));
    }
}
