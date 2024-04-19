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

final readonly class NodeScanCommand
{
    /**
     * @param array<int,string> $targets
     */
    public function __construct(
        private array $targets
    ) {
    }

    /**
     * @return array<int,string>
     */
    public function getTargets(): array
    {
        return $this->targets;
    }
}
