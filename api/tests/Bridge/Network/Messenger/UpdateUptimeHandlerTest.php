<?php

namespace Reboot\Tests\Bridge\Network\Messenger;

use Hoa\Iterator\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Reboot\Bridge\Network\Messenger\UpdateUptimeHandler;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\Network\NetworkException;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Network\UpdateUptimeRequest;

class UpdateUptimeHandlerTest extends TestCase
{
    private MockObject|NodeRepositoryInterface $nodeRepository;
    private MockObject|SshFactoryInterface $sshFactory;
    private MockObject|SshInterface $ssh;
    private MockObject|NodeInterface $node;
    private MockObject|LoggerInterface $logger;

    private UpdateUptimeHandler $handler;


    public function setUp(): void
    {
        $this->nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $this->sshFactory = $this->createMock(SshFactoryInterface::class);
        $this->ssh = $this->createMock(SshInterface::class);
        $this->node = $this->createMock(NodeInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sshFactory->method('createSshClient')
            ->with($this->node)
            ->willReturn($this->ssh);
        $this->nodeRepository->method('findById')
            ->willReturn($this->node);

        $this->handler = new UpdateUptimeHandler(
            $this->nodeRepository,
            $this->sshFactory,
            $this->logger
        );
    }

    public function testInvoke(): void
    {
        $ssh = $this->ssh;
        $handler = $this->handler;

        $ssh->expects($this->once())
            ->method('addCommand')
            ->with('uptime -s')
            ;
        $ssh->expects($this->once())
            ->method('execute');

        $ssh->expects($this->once())
            ->method('getOutputs')
            ->willReturn(['2024-04-04 21:51:29']);

        $this->nodeRepository->expects($this->once())
            ->method('store')
            ->with($this->node);

        $handler(new UpdateUptimeRequest('some-id'));

    }

    public function testInvokeWithInvalidID(): void
    {
        $nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $sshFactory = $this->sshFactory;

        $nodeRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $handler = new UpdateUptimeHandler(
            $nodeRepository,
            $sshFactory,
            $this->logger
        );

        $this->expectException(NetworkException::class);
        $handler(new UpdateUptimeRequest('some-id'));
    }
}
