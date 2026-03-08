<?php

namespace App\Tests\Entity;

use App\Entity\Campaign;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testAddAndRemoveManagedCampaign(): void
    {
        $user = new User();
        $campaign = new Campaign();

        // Initially, the collection should be empty
        $this->assertCount(0, $user->getManagedCampaigns());

        // Add a managed campaign
        $user->addManagedCampaign($campaign);
        $this->assertCount(1, $user->getManagedCampaigns());
        $this->assertTrue($user->getManagedCampaigns()->contains($campaign));

        // Add the same managed campaign again (should not duplicate)
        $user->addManagedCampaign($campaign);
        $this->assertCount(1, $user->getManagedCampaigns());

        // Remove the managed campaign
        $user->removeManagedCampaign($campaign);
        $this->assertCount(0, $user->getManagedCampaigns());
        $this->assertFalse($user->getManagedCampaigns()->contains($campaign));
    }

    public function testGetEmailAndSetEmail(): void
    {
        $user = new User();
        $email = 'test@example.com';

        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($email, $user->getUserIdentifier());
    }

    public function testRoles(): void
    {
        $user = new User();

        // Default roles
        $this->assertContains('ROLE_USER', $user->getRoles());

        // Add a role
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}
