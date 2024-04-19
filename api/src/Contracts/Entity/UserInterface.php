<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Contracts\Entity;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;

interface UserInterface extends SymfonyUser
{
    public function setEmail(string $email): self;

    public function setName(string $name): self;

    /**
     * @param array<int,string> $roles
     */
    public function setRoles(array $roles): self;
}
