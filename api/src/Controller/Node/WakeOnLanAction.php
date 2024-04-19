<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Controller\Node;

use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Messenger\Node\WakeOnLanCommand;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class WakeOnLanAction
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function __invoke(
        NodeInterface $node
    ): NodeInterface {
        $nodeId = $node->getId();
        $command = new WakeOnLanCommand($nodeId);
        $this->messageBus->dispatch($command);

        return $node;
    }
}
