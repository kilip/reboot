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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Node\NodeCommandException;
use Reboot\Messenger\Node\PowerOffCommand;
use Reboot\Messenger\Node\PowerOffHandler;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class PowerOffHandlerTest extends TestCase
{
    private NodeRepositoryInterface|MockObject $nodeRepository;
    private MockObject|SshFactoryInterface $sshFactory;
    private MockObject|NodeInterface $node;
    private MockObject|SshInterface $ssh;
    private MockObject|HubInterface $mercureHub;

    private PowerOffHandler $handler;

    protected function setUp(): void
    {
        $this->nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $this->sshFactory = $this->createMock(SshFactoryInterface::class);
        $this->node = $this->createMock(NodeInterface::class);
        $this->ssh = $this->createMock(SshInterface::class);
        $this->mercureHub = $this->createMock(HubInterface::class);
        $this->handler = new PowerOffHandler(
            $this->nodeRepository,
            $this->sshFactory,
            $this->mercureHub
        );
    }

    public function testInvoke(): void
    {
        $ssh = $this->ssh;
        $handler = $this->handler;

        $this->configureBaseException();

        $error = <<<EOC
failed: Failed to execute command "sudo poweroff". Error message: "Connection closed (by server) prematurely 1."
EOC;

        $ssh->expects($this->once())
            ->method('addCommand')
            ->with("sudo poweroff");

        $ssh->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception($error));

        $this->mercureHub
            ->expects($this->once())
            ->method('publish')
            ->with($this->isInstanceOf(Update::class));

        $handler(new PowerOffCommand('some-id'));
    }

    public function testThrowOnInvalidNodeId(): void
    {
        $nodeRepository = $this->nodeRepository;
        $handler = $this->handler;

        $nodeRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(NodeCommandException::class);

        $handler(new PowerOffCommand('some-id'));
    }

    public function testWhenNodeAlreadyOffline(): void
    {
        $handler = $this->handler;
        $this->nodeRepository->expects($this->once())
            ->method('findById')
            ->with($this->anything())
            ->willReturn($this->node);

        $this->node->expects($this->once())
            ->method('isOnline')
            ->willReturn(false);

        $this->mercureHub->expects($this->once())
            ->method('publish');

        $handler(new PowerOffCommand('some-id'));
    }

    private function configureBaseException(): void
    {
        $this->nodeRepository->expects($this->once())
            ->method('findById')
            ->with($this->anything())
            ->willReturn($this->node);

        $this->sshFactory->expects($this->once())
            ->method('createSshClient')
            ->with($this->node)
            ->willReturn($this->ssh);

        $this->node->expects($this->once())
            ->method('isOnline')
            ->willReturn(true);
    }
}
