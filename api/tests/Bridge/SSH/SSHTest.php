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

use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\SSH\SSH;
use Reboot\Bridge\SSH\SshException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SSHTest extends TestCase
{
    private MockObject|HubInterface $mercureHub;

    private MockObject|SSH2 $client;

    private SSH $sut;

    protected function setUp(): void
    {
        $privateKey = file_get_contents(__DIR__.'/fixtures/private');
        $this->mercureHub = $this->createMock(HubInterface::class);
        $this->client = $this->createMock(SSH2::class);

        $this->sut = new SSH(
            host: 'localhost',
            username: 'user',
            privateKey: PublicKeyLoader::load($privateKey),
            mercureHub: $this->mercureHub,
            ssh: $this->client
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

    public function testExecute(): void
    {
        $ssh = $this->sut;
        $client = $this->client;

        $client->expects($this->once())
            ->method('login')
            ->with('user', $this->isInstanceOf(AsymmetricKey::class))
            ->willReturn(true);

        $client->expects($this->once())
            ->method('exec')
            ->with('some command');

        $ssh->addCommand('some command');
        $ssh->execute();
    }

    public function testExecuteWithFailedLogin(): void
    {
        $ssh = $this->sut;
        $client = $this->client;

        $client->expects($this->once())
            ->method('login')
            ->willReturn(false);

        $this->expectException(SshException::class);
        $ssh->addCommand('some command');
        $ssh->execute();
    }

    public function testExecuteWithFailedCommand(): void
    {
        $ssh = $this->sut;
        $client = $this->client;

        $client->expects($this->once())
            ->method('login')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('exec')
            ->with('some command')
            ->willThrowException(new \Exception('some exception'));

        $this->expectException(SshException::class);
        $ssh->addCommand('some command');
        $ssh->execute();
    }
}
