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

use phpseclib3\Crypt\PublicKeyLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\SSH\SSH;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SSHTest extends TestCase
{
    private MockObject|HubInterface $mercureHub;

    private SSH $sut;

    protected function setUp(): void
    {
        $privateKey = file_get_contents(__DIR__.'/fixtures/private');
        $this->mercureHub = $this->createMock(HubInterface::class);

        $this->sut = new SSH(
            host: 'localhost',
            username: 'user',
            privateKey: PublicKeyLoader::load($privateKey),
            mercureHub: $this->mercureHub
        );
    }

    public function testCommands(): void
    {
        $ssh = $this->sut;
        $this->assertEmpty($ssh->getCommands());
        $ssh->addCommand($command = 'sudo poweroff');
        $this->assertNotEmpty($ssh->getCommands());
        $this->assertContains($command, $ssh->getCommands());
    }

    public function testPublishOutput(): void
    {
        $ssh = $this->sut;

        $this->mercureHub->expects($this->once())
            ->method('getPublicUrl')
            ->willReturn('https://localhost');

        $this->mercureHub->expects($this->once())
            ->method('publish')
            ->with($this->isInstanceOf(Update::class));

        $ssh->publishOutput('hello world');
    }
}
