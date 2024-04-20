<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\SSH;

use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Net\SFTP as SftpClient;
use Reboot\Tests\Bridge\Network\SftpInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final readonly class SFTP implements SftpInterface
{
    public function __construct(
        private string $host,
        private string $username,
        private AsymmetricKey $privateKey,
        private HubInterface $mercureHub,
        private int $port = 22,
        private int $timeout = 10,
        private ?SftpClient $client = null
    ) {
    }

    public function downloadFile(string $remote, string $destination): void
    {
        $sftp = $this->client ?? new SftpClient($this->host, $this->port, $this->timeout);
        $mercure = $this->mercureHub;
        $topic = $mercure->getPublicUrl().'/sftp';

        if (!$sftp->login($this->username, $this->privateKey)) {
            throw SshException::failedToLogin($this->host, $this->username, $this->port);
        }

        try {
            $output = $sftp->get($remote, $destination);
            $data = [
                'host' => $this->host,
                'username' => $this->username,
                'output' => $output,
            ];
            $mercure->publish(new Update($topic,
                json_encode($data, JSON_THROW_ON_ERROR),
            ));
        } catch (\Exception $e) {
            throw SshException::failedToDownloadFile($this->host, $this->username, $this->port, $remote, $destination, $e->getMessage());
        }
    }
}
