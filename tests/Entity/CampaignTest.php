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

    public function testRemoveAssetEdgeCases(): void
    {
        $campaign1 = new Campaign();
        $campaign2 = new Campaign();
        $asset = new Asset();

        // 1. Removing an asset that is NOT in the collection
        // Let's set the asset's campaign to campaign2
        $campaign2->addAsset($asset);
        $this->assertSame($campaign2, $asset->getCampaign());

        // Now try to remove it from campaign1
        $campaign1->removeAsset($asset);

        // The asset should still belong to campaign2
        $this->assertSame($campaign2, $asset->getCampaign());
        $this->assertCount(0, $campaign1->getAssets());
        $this->assertCount(1, $campaign2->getAssets());

        // 2. Removing an asset from campaign1, but the asset's owning side (getCampaign)
        // has already been changed to point to something else.
        $asset2 = new Asset();
        $campaign1->addAsset($asset2);
        $this->assertSame($campaign1, $asset2->getCampaign());

        // Manually change the owning side to campaign2 (simulating a state change before removal)
        $asset2->setCampaign($campaign2);

        // Remove from campaign1
        $campaign1->removeAsset($asset2);

        // It should be removed from the collection
        $this->assertCount(0, $campaign1->getAssets());
        // But the owning side should NOT be set to null, it should still be campaign2
        $this->assertSame($campaign2, $asset2->getCampaign());
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

    public function testRemoveTransactionEdgeCases(): void
    {
        $campaign1 = new Campaign();
        $campaign2 = new Campaign();
        $transaction = new Transaction();

        // 1. Removing a transaction that is NOT in the collection
        // Let's set the transaction's campaign to campaign2
        $campaign2->addTransaction($transaction);
        $this->assertSame($campaign2, $transaction->getCampaign());

        // Now try to remove it from campaign1
        $campaign1->removeTransaction($transaction);

        // The transaction should still belong to campaign2
        $this->assertSame($campaign2, $transaction->getCampaign());
        $this->assertCount(0, $campaign1->getTransactions());
        $this->assertCount(1, $campaign2->getTransactions());

        // 2. Removing a transaction from campaign1, but the transaction's owning side (getCampaign)
        // has already been changed to point to something else.
        $transaction2 = new Transaction();
        $campaign1->addTransaction($transaction2);
        $this->assertSame($campaign1, $transaction2->getCampaign());

        // Manually change the owning side to campaign2 (simulating a state change before removal)
        $transaction2->setCampaign($campaign2);

        // Remove from campaign1
        $campaign1->removeTransaction($transaction2);

        // It should be removed from the collection
        $this->assertCount(0, $campaign1->getTransactions());
        // But the owning side should NOT be set to null, it should still be campaign2
        $this->assertSame($campaign2, $transaction2->getCampaign());
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

    public function testRemoveManagerEdgeCases(): void
    {
        $campaign1 = new Campaign();
        $campaign2 = new Campaign();
        $manager = new User();

        // 1. Removing a manager that is NOT in the collection
        // Let's add the manager to campaign2
        $campaign2->addManager($manager);
        $this->assertTrue($manager->getManagedCampaigns()->contains($campaign2));

        // Now try to remove the manager from campaign1
        $campaign1->removeManager($manager);

        // The manager should still be managing campaign2
        $this->assertTrue($manager->getManagedCampaigns()->contains($campaign2));
        $this->assertFalse($manager->getManagedCampaigns()->contains($campaign1));
        $this->assertCount(0, $campaign1->getManagers());
        $this->assertCount(1, $campaign2->getManagers());

        // 2. Ensuring inverse side is not broken if we try to remove when already not matching
        $manager2 = new User();
        $campaign1->addManager($manager2);

        $campaign1->removeManager($manager2);
        // Repeated remove
        $campaign1->removeManager($manager2);
        $this->assertCount(0, $campaign1->getManagers());
        $this->assertFalse($manager2->getManagedCampaigns()->contains($campaign1));
    }
}
