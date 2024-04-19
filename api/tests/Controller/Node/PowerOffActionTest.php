<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Controller\Node;

use PHPUnit\Framework\TestCase;
use Reboot\Controller\Node\PowerOffAction;
use Reboot\Entity\Node;
use Reboot\Messenger\Node\PowerOffCommand;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class PowerOffActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $node = $this->createMock(Node::class);
        $action = new PowerOffAction($bus);

        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PowerOffCommand::class))
            ->willReturn(new Envelope(new \stdClass()));

        $node->expects($this->once())
            ->method('getId')
            ->willReturn(Uuid::v4());

        $action($node);
    }
}
