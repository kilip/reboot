<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Bridge\Network;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\Network\NetworkException;
use Reboot\Bridge\Network\NodeScanner;
use Reboot\Contracts\SftpInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Node\NodeFoundNotification;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ScannerTest extends TestCase
{
    private MockObject|SshInterface $ssh;
    private MockObject|SftpInterface $sftp;
    private MockObject|MessageBusInterface $messageBus;

    private NodeScanner $scanner;

    protected function setUp(): void
    {
        $this->ssh = $this->createMock(SshInterface::class);
        $this->sftp = $this->createMock(SftpInterface::class);
        $remoteResult = '/tmp/reboot/scanner/result.xml';
        $localResult = __DIR__.'/fixtures/scan-result.xml';

        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->scanner = new NodeScanner(
            target: '192.168.0.0/24',
            ssh: $this->ssh,
            sftp: $this->sftp,
            messageBus: $this->messageBus,
            remoteResultFile: $remoteResult,
            localResultFile: $localResult
        );
    }

    public function testRun(): void
    {
        $ssh = $this->ssh;
        $sftp = $this->sftp;
        $messageBus = $this->messageBus;
        $scanner = $this->scanner;

        $ssh->expects($this->exactly(2))
            ->method('addCommand');

        $ssh->expects($this->once())
            ->method('execute');

        $sftp->expects($this->once())
            ->method('downloadFile');

        $messageBus->expects($this->exactly(4))
            ->method('dispatch')
            ->with($this->isInstanceOf(NodeFoundNotification::class))
            ->willReturn(new Envelope(new \stdClass()));

        $scanner->run();
    }

    public function testRunWithNoResultFile(): void
    {
        $scanner = new NodeScanner(
            target: '192.168.0.0/24',
            ssh: $this->ssh,
            sftp: $this->sftp,
            messageBus: $this->messageBus,
            remoteResultFile: '/tmp/result.xml',
            localResultFile: '/tmp/result.xml'
        );

        $this->expectException(NetworkException::class);

        $scanner->run();
    }
}
