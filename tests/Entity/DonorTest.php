<?php

namespace App\Tests\Entity;

use App\Entity\Donor;
use App\Entity\Transaction;
use PHPUnit\Framework\TestCase;

class DonorTest extends TestCase
{
    public function testAddAndRemoveTransaction(): void
    {
        $donor = new Donor();
        $transaction = new Transaction();

        // Initially, the collection should be empty
        $this->assertCount(0, $donor->getTransactions());

        // Add a transaction
        $donor->addTransaction($transaction);
        $this->assertCount(1, $donor->getTransactions());
        $this->assertTrue($donor->getTransactions()->contains($transaction));
        $this->assertSame($donor, $transaction->getDonor());

        // Add the same transaction again (should not duplicate)
        $donor->addTransaction($transaction);
        $this->assertCount(1, $donor->getTransactions());

        // Remove the transaction
        $donor->removeTransaction($transaction);
        $this->assertCount(0, $donor->getTransactions());
        $this->assertNull($transaction->getDonor());
    }

    public function testRemoveTransactionEdgeCases(): void
    {
        $donor1 = new Donor();
        $donor2 = new Donor();
        $transaction = new Transaction();

        // 1. Removing a transaction that is NOT in the collection
        // Let's set the transaction's donor to donor2
        $donor2->addTransaction($transaction);
        $this->assertSame($donor2, $transaction->getDonor());

        // Now try to remove it from donor1
        $donor1->removeTransaction($transaction);

        // The transaction should still belong to donor2
        $this->assertSame($donor2, $transaction->getDonor());
        $this->assertCount(0, $donor1->getTransactions());
        $this->assertCount(1, $donor2->getTransactions());

        // 2. Removing a transaction from donor1, but the transaction's owning side (getDonor)
        // has already been changed to point to something else.
        $transaction2 = new Transaction();
        $donor1->addTransaction($transaction2);
        $this->assertSame($donor1, $transaction2->getDonor());

        // Manually change the owning side to donor2 (simulating a state change before removal)
        $transaction2->setDonor($donor2);

        // Remove from donor1
        $donor1->removeTransaction($transaction2);

        // It should be removed from the collection
        $this->assertCount(0, $donor1->getTransactions());
        // But the owning side should NOT be set to null, it should still be donor2
        $this->assertSame($donor2, $transaction2->getDonor());
    }
}
