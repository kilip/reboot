<?php

namespace Reboot\Contracts;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;
interface UserInterface extends SymfonyUser
{

    public function setEmail(string $email): self;

    public function setName(string $name): self;
}
