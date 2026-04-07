<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends TestCase
{
    private function createRegistryAndEm(): array
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = new ClassMetadata(User::class);

        $registry->method('getManagerForClass')->willReturn($em);
        $em->method('getClassMetadata')->willReturn($classMetadata);

        return [$registry, $em];
    }

    public function testUpgradePasswordThrowsExceptionForUnsupportedUser(): void
    {
        [$registry, $em] = $this->createRegistryAndEm();

        $repository = new UserRepository($registry);

        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage(sprintf('Instances of "%s" are not supported.', get_class($unsupportedUser)));

        $repository->upgradePassword($unsupportedUser, 'new_hashed_password');
    }

    public function testUpgradePasswordUpdatesAndPersistsUser(): void
    {
        [$registry, $em] = $this->createRegistryAndEm();

        $user = new User();
        $newPassword = 'new_hashed_password';

        $em->expects($this->once())
            ->method('persist')
            ->with($user);

        $em->expects($this->once())
            ->method('flush');

        $repository = new UserRepository($registry);
        $repository->upgradePassword($user, $newPassword);

        $this->assertSame($newPassword, $user->getPassword());
    }
}
