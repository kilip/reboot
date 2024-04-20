<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Bridge\SSH;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\SSH\SFTP;
use Reboot\Bridge\SSH\SSH;
use Reboot\Bridge\SSH\SshFactory;
use Reboot\Contracts\Entity\NodeInterface;
use Symfony\Component\Mercure\HubInterface;

class SshFactoryTest extends TestCase
{
    private HubInterface|MockObject $mercureHub;

    private NodeInterface|MockObject $node;

    private SshFactory $factory;

    protected function setUp(): void
    {
        $this->mercureHub = $this->createMock(HubInterface::class);
        $this->node = $this->createMock(NodeInterface::class);

        $this->factory = new SshFactory(
            mercureHub: $this->mercureHub,
            defaultPrivateKey: __DIR__.'/fixtures/private'
        );
    }

    public function testCreateSshClient(): void
    {
        $factory = $this->factory;
        $node = $this->node;
        $this->setNodeExpectation($node);
        $ssh = $factory->createSshClient($node);
        $this->assertInstanceOf(SSH::class, $ssh);
    }

    public function testCreateSftpClient(): void
    {
        $factory = $this->factory;
        $node = $this->node;

        $this->setNodeExpectation($node);

        $sftp = $factory->createSftpClient($node);

        $this->assertInstanceOf(SFTP::class, $sftp);
    }

    protected function setNodeExpectation(NodeInterface|MockObject $node): void
    {
        $node->expects($this->once())
            ->method('getSshPrivateKey')
            ->willReturn(null);

        $node->expects($this->once())
            ->method('getSshUser')
            ->willReturn(null);

        $node->expects($this->once())
            ->method('getSshPort')
            ->willReturn(null);
    }
}
