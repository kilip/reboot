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
use Reboot\Entity\Node;
use Reboot\Messenger\Node\PowerOffCommand;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
final readonly class PowerOffAction
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(
        Node $node,
    ): NodeInterface {
        $command = new PowerOffCommand($node->getId());
        $this->messageBus->dispatch($command);

        return $node;
    }
}
