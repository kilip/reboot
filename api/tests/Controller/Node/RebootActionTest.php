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
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Controller\Node\RebootAction;
use Reboot\Messenger\Node\RebootCommand;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class RebootActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $node = $this->createMock(NodeInterface::class);
        $action = new RebootAction($bus);

        $node->expects($this->once())
            ->method('getId')
            ->willReturn(Uuid::v4());

        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RebootCommand::class))
            ->willReturn(new Envelope(new \stdClass()));

        $this->assertSame(
            $node,
            $action($node)
        );
    }
}
