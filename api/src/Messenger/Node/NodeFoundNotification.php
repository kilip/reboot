<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Messenger\Node;

final readonly class NodeFoundNotification
{
    public function __construct(
        private string $ipAddress,
        private string $hostname,
        private ?string $vendor,
        private ?string $macAddress,
    ) {
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function getMacAddress(): ?string
    {
        return $this->macAddress;
    }
}
