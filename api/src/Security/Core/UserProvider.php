<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Security\Core;

use Reboot\Contracts\Entity\UserInterface;
use Reboot\Contracts\Entity\UserRepositoryInterface;
use Reboot\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;

/**
 * @implements AttributesBasedUserProviderInterface<UserInterface|User|SymfonyUser>
 */
class UserProvider implements AttributesBasedUserProviderInterface
{
    /**
     * Map authentik group into reboot role.
     *
     * @var array<string,string>
     */
    private array $groupMaps = [
        'Admin' => 'ROLE_ADMIN',
    ];

    public function __construct(
        private UserRepositoryInterface $users
    ) {
    }

    public function refreshUser(SymfonyUser $user): SymfonyUser
    {
        $this->users->refresh($user);

        return $user;
    }

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

        if (!$user instanceof User) {
            $user = $users->create();
        }

        if (!isset($attributes['name'])) {
            throw new UnsupportedUserException('Property "name" is missing in token attributes.');
        }

        // TODO: automatically set roles from groups
        $user->setEmail($attributes['email']);
        $user->setName($attributes['name']);
        $user->setRoles($this->generateRoles($attributes));

        $users->store($user);

        return $user;
    }

    /**
     * @param array<string,mixed> $attributes
     *
     * @return array<int,string>
     */
    private function generateRoles(array $attributes): array
    {
        // ensure ROLE_USER always exists
        $roles = ['ROLE_USER'];

        if (array_key_exists('groups', $attributes)) {
            foreach ($attributes['groups'] as $group) {
                if (array_key_exists($group, $this->groupMaps)) {
                    $roles[] = $this->groupMaps[$group];
                }
            }
        }

        return $roles;
    }
}
