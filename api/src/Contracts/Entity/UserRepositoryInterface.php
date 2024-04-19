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

interface UserRepositoryInterface
{
    public function refresh(object $user): void;

    public function getClass(): string;

    public function findByEmail(string $identifier): ?UserInterface;

    public function create(): UserInterface;

    public function store(UserInterface $user): void;
}
