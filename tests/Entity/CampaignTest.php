<?php

namespace App\Tests\Entity;

use App\Entity\Campaign;
use App\Entity\Transaction;
use PHPUnit\Framework\TestCase;

class CampaignTest extends TestCase
{
    public function testAddTransaction(): void
    {
        $campaign = new Campaign();
        $transaction = new Transaction();

        $campaign->addTransaction($transaction);

        $this->assertCount(1, $campaign->getTransactions());
        $this->assertTrue($campaign->getTransactions()->contains($transaction));
        $this->assertSame($campaign, $transaction->getCampaign());
    }

    public function testAddTransactionIdempotency(): void
    {
        $campaign = new Campaign();
        $transaction = new Transaction();

        $campaign->addTransaction($transaction);
        $campaign->addTransaction($transaction);

        $this->assertCount(1, $campaign->getTransactions());
    }

    public function testRemoveTransaction(): void
    {
        $campaign = new Campaign();
        $transaction = new Transaction();

        $campaign->addTransaction($transaction);
        $this->assertSame($campaign, $transaction->getCampaign());

        $campaign->removeTransaction($transaction);

        $this->assertCount(0, $campaign->getTransactions());
        $this->assertNull($transaction->getCampaign());
    }

    public function testRemoveTransactionBiDirectional(): void
    {
        $campaign = new Campaign();
        $transaction = new Transaction();

        $campaign->addTransaction($transaction);
        $transaction->setCampaign(null); // Manually break it from one side

        $campaign->removeTransaction($transaction);
        $this->assertCount(0, $campaign->getTransactions());
    }

    public function testGetTransactions(): void
    {
        $campaign = new Campaign();
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $campaign->getTransactions());
    }
}
