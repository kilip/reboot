<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Bridge\Network\Messenger;

use ApiPlatform\Doctrine\Orm\AbstractPaginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\Network\Messenger\NodeStatusUpdateHandler;
use Reboot\Bridge\Network\ResultParser;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SftpInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Network\NodeStatusUpdateRequest;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class NodeStatusUpdateHandlerTest extends TestCase
{
    private MockObject|NodeRepositoryInterface $nodeRepository;
    private MockObject|AbstractPaginator $paginator;
    private MockObject|SshFactoryInterface $sshFactory;
    private MockObject|SshInterface $ssh;
    private MockObject|SftpInterface $sftp;
    private MockObject|NodeInterface $node;
    private MockObject|MessageBusInterface $messageBus;

    private MockObject|ResultParser $resultParser;

    private NodeStatusUpdateHandler $handler;

    protected function setUp(): void
    {
        $this->nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $this->ssh = $this->createMock(SshInterface::class);
        $this->sftp = $this->createMock(SftpInterface::class);
        $this->sshFactory = $this->createMock(SshFactoryInterface::class);
        $this->paginator = $this->createMock(AbstractPaginator::class);
        $this->node = $this->createMock(NodeInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->resultParser = $this->createMock(ResultParser::class);

        $this->sshFactory->method('createSshClient')
            ->with($this->node)
            ->willReturn($this->ssh);
        $this->sshFactory->method('createSftpClient')
            ->with($this->node)
            ->willReturn($this->sftp);

        $this->nodeRepository
            ->method('getPaginator')
            ->willReturn($this->paginator);
        $this->nodeRepository
            ->method('getNavigator')
            ->willReturn($this->node);

        $this->paginator->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->node, $this->node, $this->node, $this->node, $this->node]));

        $this->node->method('getId')->willReturn(Uuid::v1());

        $this->handler = new NodeStatusUpdateHandler(
            nodeRepository: $this->nodeRepository,
            sshFactory: $this->sshFactory,
            messageBus: $this->messageBus,
            remoteTempPath: '/tmp/reboot',
            cachePath: '/tmp/reboot',
            resultParser: $this->resultParser
        );
    }

    public function testInvoke(): void
    {
        $this->node->expects($this->exactly(5))
            ->method('getIpAddress')
            ->willReturn(
                '192.168.1.1',
                '192.168.1.2',
                '192.168.1.3',
                '192.168.1.4',
                '192.168.1.5',
            )
        ;

        $this->node->expects($this->exactly(5))
            ->method('setOnline');

        $this->ssh->expects($this->exactly(2))
            ->method('addCommand');

        $this->ssh->expects($this->once())
            ->method('execute');

        $this->sftp->expects($this->once())
            ->method('downloadFile');

        $this->messageBus->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        $this->resultParser->expects($this->once())
            ->method('parse');
        $this->resultParser->expects($this->once())
            ->method('getOnlineIps')
            ->willReturn([
                '192.168.1.1',
                '192.168.1.2',
                '192.168.1.3',
            ]);

        $handler = $this->handler;
        $handler(new NodeStatusUpdateRequest(0, 5));
    }
}
