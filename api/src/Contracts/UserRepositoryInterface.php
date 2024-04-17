<?php

namespace Reboot\Contracts;

interface UserRepositoryInterface
{
    public function refresh(object $user): void;

    public function getClass(): string;

    public function findByEmail(string $identifier): ?UserInterface;

    public function create(): UserInterface;

    public function store(UserInterface $user): void;
}
