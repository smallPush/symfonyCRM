<?php

namespace App\Tests\Security\Voter;

use App\Entity\Campaign;
use App\Entity\User;
use App\Security\Voter\CampaignVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Common\Collections\ArrayCollection;

class CampaignVoterTest extends TestCase
{
    private function createVoter(): CampaignVoter
    {
        return new CampaignVoter();
    }

    public function testVoteWithAnonymousUser(): void
    {
        $voter = $this->createVoter();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $campaign = $this->createMock(Campaign::class);

        $result = $voter->vote($token, $campaign, [CampaignVoter::EDIT]);

        $this->assertEquals(Voter::ACCESS_DENIED, $result);
    }

    public function testVoteWithAdminUser(): void
    {
        $voter = $this->createVoter();
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN', 'ROLE_USER']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $campaign = $this->createMock(Campaign::class);

        $result = $voter->vote($token, $campaign, [CampaignVoter::EDIT]);

        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testVoteWithManagerUser(): void
    {
        $voter = $this->createVoter();
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $campaign = $this->createMock(Campaign::class);
        $managers = new ArrayCollection([$user]);
        $campaign->method('getManagers')->willReturn($managers);

        $result = $voter->vote($token, $campaign, [CampaignVoter::EDIT]);

        $this->assertEquals(Voter::ACCESS_GRANTED, $result);
    }

    public function testVoteWithNonManagerUser(): void
    {
        $voter = $this->createVoter();
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getManagers')->willReturn(new ArrayCollection());

        $result = $voter->vote($token, $campaign, [CampaignVoter::EDIT]);

        $this->assertEquals(Voter::ACCESS_DENIED, $result);
    }
}
