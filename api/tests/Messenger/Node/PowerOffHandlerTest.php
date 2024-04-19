<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Messenger\Node;

use PHPUnit\Framework\TestCase;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Node\PowerOffCommand;
use Reboot\Messenger\Node\PowerOffHandler;

class PowerOffHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $sshFactory = $this->createMock(SshFactoryInterface::class);
        $node = $this->createMock(NodeInterface::class);
        $ssh = $this->createMock(SshInterface::class);
        $handler = new PowerOffHandler(
            $nodeRepository,
            $sshFactory
        );

        $nodeRepository->expects($this->once())
            ->method('findById')
            ->with('some-id')
            ->willReturn($node);

        $sshFactory->expects($this->once())
            ->method('create')
            ->with($node)
            ->willReturn($ssh);

        $ssh->expects($this->once())
            ->method('addCommand')
            ->with('sudo poweroff');

        $handler(new PowerOffCommand('some-id'));
    }
}
