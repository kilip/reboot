<?php

namespace Reboot\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Reboot\Contracts\UserInterface;
use Reboot\Contracts\UserRepositoryInterface;
use Reboot\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @extends ServiceEntityRepository<User>
 */
#[AsAlias(id: UserRepositoryInterface::class)]
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function refresh(object $user): void
    {
        $this->getEntityManager()->refresh($user);
    }

    public function getClass(): string
    {
        return User::class;
    }

    public function findByEmail(string $identifier): ?UserInterface
    {
        return $this->findOneBy(['email' => $identifier]);
    }

    public function create(): UserInterface
    {
        return new User();
    }

    public function store(UserInterface $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
