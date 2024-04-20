<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\Network;

final readonly class ResultNode
{
    public function __construct(
        private string $hostname,
        private string $ipAddress,
        private ?string $mac = null,
        private ?string $vendor = null
    ) {
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getMac(): ?string
    {
        return $this->mac;
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }
}
