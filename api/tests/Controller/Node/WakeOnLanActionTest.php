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
use Reboot\Controller\Node\WakeOnLanAction;
use Reboot\Entity\Node;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class WakeOnLanActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $node = $this->createMock(Node::class);
        $bus = $this->createMock(MessageBusInterface::class);
        $sut = new WakeOnLanAction($bus);

        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        $node->expects($this->once())
            ->method('getId')
            ->willReturn(Uuid::v4());

        $sut($node);
    }
}
