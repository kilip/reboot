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
use Reboot\Messenger\Node\WakeOnLanCommand;
use Reboot\Messenger\Node\WakeOnLanHandler;

class WakeOnLanHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $sshFactory = $this->createMock(SshFactoryInterface::class);
        $nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $ssh = $this->createMock(SshInterface::class);
        $node = $this->createMock(NodeInterface::class);
        $handler = new WakeOnLanHandler($nodeRepository, $sshFactory);

        $nodeRepository->expects($this->once())
            ->method('findByIpAddress')
            ->with('localhost')
            ->willReturn($node)
        ;

        $nodeRepository->expects($this->once())
            ->method('findById')
            ->with('some-id')
            ->willReturn($node);

        $node->expects($this->once())
            ->method('getMacAddress')
            ->willReturn($mac = 'aa:bb:cc:dd:ee:ff');

        $sshFactory->expects($this->once())
            ->method('createSshClient')
            ->with($node)
            ->willReturn($ssh);

        $ssh->expects($this->once())
            ->method('addCommand')
            ->with("wakeonlan {$mac}");

        $ssh->expects($this->once())
            ->method('execute');

        $handler(new WakeOnLanCommand('some-id'));
    }
}
