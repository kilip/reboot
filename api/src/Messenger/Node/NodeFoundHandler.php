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

use Reboot\Bridge\Network\ResultNode;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class NodeFoundHandler
{
    public function __invoke(ResultNode $newNode): void
    {
        // TODO: Implement __invoke() method.
    }
}
