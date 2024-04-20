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

use Symfony\Component\Uid\Uuid;

interface NodeInterface
{
    public function getId(): ?Uuid;

    public function setHostname(string $hostname): self;

    public function getHostname(): ?string;

    public function setMacAddress(string $macAddress): self;

    public function getMacAddress(): ?string;

    public function setIpAddress(string $ipAddress): self;

    public function getIpAddress(): string;

    public function getSshPort(): ?int;

    public function getSshPrivateKey(): ?string;

    public function getSshUser(): ?string;

    public function setOnline(bool $status): self;

    public function isOnline(): bool;

    public function setUptime(\DateTimeImmutable $uptime = null): self;

    public function getUptime(): ?\DateTimeImmutable;

    public function setDraft(bool $draft): self;

    public function isDraft(): bool;
}
