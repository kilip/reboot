<?php

namespace Reboot\Security\Core;

use Reboot\Contracts\UserRepositoryInterface;
use Reboot\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;
use Reboot\Contracts\UserInterface;

/**
 * @implements AttributesBasedUserProviderInterface<UserInterface|User|SymfonyUser>
 */
class UserProvider implements AttributesBasedUserProviderInterface
{

    public function __construct(
        private UserRepositoryInterface $users
    )
    {
    }

    public function refreshUser(SymfonyUser $user): SymfonyUser
    {
        $this->users->refresh($user);
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class): bool
    {
        return $class == $this->users->getClass();
    }

    /**
     * @param array<string,mixed> $attributes
     */
    public function loadUserByIdentifier(string $identifier, array $attributes = []): SymfonyUser
    {
        $users = $this->users;
        $user = $users->findByEmail($identifier);

        if(!$user instanceof User){
            $user = $users->create();
        }

        if(!isset($attributes['name'])){
            throw new UnsupportedUserException('Property "name" is missing in token attributes.');
        }

        // TODO: automatically set roles from groups
        $user->setEmail($attributes['email']);
        $user->setName($attributes['name']);

        $users->store($user);

        return $user;
    }
}
