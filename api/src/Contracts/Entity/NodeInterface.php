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

    public function getMacAddress(): ?string;

    public function getIpAddress(): string;

    public function getSshPort(): ?int;

    public function getSshPrivateKey(): ?string;

    public function getSshUser(): ?string;
}
