<?php

namespace Reboot\Tests\Bridge\Network;

use PHPUnit\Framework\MockObject\MockObject;
use Reboot\Bridge\Network\NetworkException;
use Reboot\Bridge\Network\NetworkFactory;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\Network\Scanner;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SftpInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class NetworkFactoryTest extends TestCase
{
    private MockObject|NodeRepositoryInterface $nodeRepository;
    private MockObject|SshFactoryInterface $sshFactory;
    private MockObject|NodeInterface $node;
    private MockObject|SshInterface $ssh;
    private MockObject|SftpInterface $sftp;

    private MessageBusInterface $messageBus;
    private NetworkFactory $factory;

    protected function setUp(): void
    {
        $this->nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $this->sshFactory = $this->createMock(SshFactoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->node = $this->createMock(NodeInterface::class);
        $this->ssh = $this->createMock(SshInterface::class);
        $this->sftp = $this->createMock(SftpInterface::class);

        $this->nodeRepository
            ->method('findByIpAddress')
            ->with('navigator')
            ->willReturn($this->node);
        $this->sshFactory->method('createSshClient')
            ->with($this->node)
            ->willReturn($this->ssh);

        $this->factory = new NetworkFactory(
            $this->nodeRepository,
            $this->sshFactory,
            $this->messageBus,
            'navigator'
        );
    }

    public function testCreateNodeScanner()
    {
        $factory = $this->factory;

        $return = $factory->createNodeScanner('10.0.0.1');

        $this->assertInstanceOf(Scanner::class, $return);
    }

    public function testWithInvalidNavigator()
    {
        $nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $nodeRepository->expects($this->any())
            ->method('findByIpAddress')
            ->willReturn(null);

        $factory = new NetworkFactory(
            $nodeRepository,
            $this->sshFactory,
            $this->messageBus,
            'invalid-navigator'
        );
        $this->expectException(NetworkException::class);
        $factory->createNodeScanner('10.0.0.1');
    }
}
