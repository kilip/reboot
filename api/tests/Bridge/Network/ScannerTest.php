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

use PHPUnit\Framework\TestCase;
use Reboot\Bridge\Network\Scanner;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Node\NodeFoundNotification;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ScannerTest extends TestCase
{
    public function testRun(): void
    {
        $ssh = $this->createMock(SshInterface::class);
        $sftp = $this->createMock(SftpInterface::class);
        $remoteResult = '/tmp/reboot/scanner/result.xml';
        $localResult = __DIR__.'/fixtures/scan-result.xml';
        // $expectedNmapCommand = 'nmap -sn -sP -oX /tmp/reboot/scanner/result.xml 192.168.0.0/24';
        $messageBus = $this->createMock(MessageBusInterface::class);
        $scanner = new Scanner(
            target: '192.168.0.0/24',
            ssh: $ssh,
            sftp: $sftp,
            messageBus: $messageBus,
            remoteResultFile: $remoteResult,
            localResultFile: $localResult
        );

        $ssh->expects($this->exactly(2))
            ->method('addCommand');

        $ssh->expects($this->once())
            ->method('execute');

        $sftp->expects($this->once())
            ->method('get')
            ->with($remoteResult, $localResult);

        $messageBus->expects($this->exactly(3))
            ->method('dispatch')
            ->with($this->isInstanceOf(NodeFoundNotification::class))
            ->willReturn(new Envelope(new \stdClass()));

        $scanner->run();
    }
}
