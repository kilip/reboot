<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Messenger\Network;

final readonly class ScanNodesCommand
{
    public function __construct(
        private string $target,
    ) {
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
