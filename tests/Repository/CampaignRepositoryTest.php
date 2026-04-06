<?php

namespace App\Tests\Repository;

use App\Entity\Campaign;
use App\Repository\CampaignRepository;
use PHPUnit\Framework\TestCase;

class CampaignRepositoryTest extends TestCase
{
    public function testFindPaginated(): void
    {
        // We use a partial mock to only mock the findBy method,
        // so we can test the logic inside findPaginated without needing a database.
        $repository = $this->getMockBuilder(CampaignRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();

        $page = 3;
        $limit = 10;

        $expectedOffset = 20; // ($page - 1) * $limit = (3 - 1) * 10 = 20

        $mockCampaigns = [
            $this->createStub(Campaign::class),
            $this->createStub(Campaign::class),
        ];

        // Set expectations for findBy
        $repository->expects($this->once())
            ->method('findBy')
            ->with(
                [], // criteria
                ['createdAt' => 'DESC'], // orderBy
                $limit, // limit
                $expectedOffset // offset
            )
            ->willReturn($mockCampaigns);

        $result = $repository->findPaginated($page, $limit);

        // Assert that the result matches what findBy returned
        $this->assertSame($mockCampaigns, $result);
    }
}
