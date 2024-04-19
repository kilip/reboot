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

use PHPUnit\Framework\TestCase;
use Reboot\Bridge\SSH\SSH;
use Reboot\Bridge\SSH\SshFactory;
use Reboot\Contracts\Entity\NodeInterface;
use Symfony\Component\Mercure\HubInterface;

class SshFactoryTest extends TestCase
{
    public function testLoadPrivateKey(): void
    {
        $mercureHub = $this->createMock(HubInterface::class);
        $factory = new SshFactory(
            mercureHub: $mercureHub,
            defaultPrivateKey: __DIR__.'/fixtures/private'
        );
        $node = $this->createMock(NodeInterface::class);

        $node->expects($this->once())
            ->method('getSshPrivateKey')
            ->willReturn(null);

        $node->expects($this->once())
            ->method('getSshUser')
            ->willReturn(null);

        $node->expects($this->once())
            ->method('getSshPort')
            ->willReturn(null);

        $ssh = $factory->createSshClient($node);

        $this->assertInstanceOf(SSH::class, $ssh);
    }
}
