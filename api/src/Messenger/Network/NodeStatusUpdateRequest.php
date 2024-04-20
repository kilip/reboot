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

final readonly class NodeStatusUpdateRequest
{
    public function __construct(
        private int $firstResult,
        private int $pageSize
    ) {
    }

    public function getFirstResult(): int
    {
        return $this->firstResult;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
