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
use phpseclib3\Net\SFTP as SftpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Bridge\SSH\SFTP;
use Reboot\Bridge\SSH\SshException;
use Symfony\Component\Mercure\HubInterface;

class SFTPTest extends TestCase
{
    private MockObject|SftpClient $client;

    private MockObject|AsymmetricKey $key;

    private MockObject|HubInterface $mercureHub;

    private SFTP $sftp;

    protected function setUp(): void
    {
        $this->client = $this->createMock(SftpClient::class);
        $this->key = $this->createMock(AsymmetricKey::class);
        $this->mercureHub = $this->createMock(HubInterface::class);
        $this->sftp = new SFTP(
            host: 'host',
            username: 'user',
            privateKey: $this->key,
            mercureHub: $this->mercureHub,
            client: $this->client
        );
    }

    public function testGet(): void
    {
        $client = $this->client;
        $sftp = $this->sftp;
        $mercureHub = $this->mercureHub;

        $client->expects($this->once())
            ->method('login')
            ->with('user', $this->key)
            ->willReturn(true);

        $client->expects($this->once())
            ->method('get')
            ->with('/tmp/foo', '/tmp/bar');

        $mercureHub->expects($this->once())
            ->method('publish');

        $sftp->downloadFile('/tmp/foo', '/tmp/bar');
    }

    public function testThrowExceptionDuringLogin(): void
    {
        $client = $this->client;
        $sftp = $this->sftp;

        $client->expects($this->once())
            ->method('login')
            ->willReturn(false);

        $this->expectException(SshException::class);
        $sftp->downloadFile('/tmp/foo', '/tmp/bar');
    }

    public function testThrowExceptionDuringFileDownload(): void
    {
        $client = $this->client;
        $sftp = $this->sftp;

        $client->expects($this->once())
            ->method('login')
            ->willReturn(true);

        $client->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('some exception'));
        $this->expectException(SshException::class);
        $sftp->downloadFile('/tmp/foo', '/tmp/bar');
    }
}
