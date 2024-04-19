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

class SFTP implements SftpInterface
{
    public function __construct(
        private readonly string $host,
        private readonly string $username,
        private readonly AsymmetricKey $privateKey,
        private readonly HubInterface $mercureHub,
        private readonly int $port = 22,
        private readonly int $timeout = 10
    ) {
    }

    public function get(string $remoteResultFile, string $localResultFile): void
    {
        $sftp = new SftpClient($this->host, $this->port, $this->timeout);
        $mercure = $this->mercureHub;
        $topic = $mercure->getPublicUrl().'/sftp';

        $sftp->login($this->username, $this->privateKey);
        $output = $sftp->get($remoteResultFile, $localResultFile);

        $data = [
            'host' => $this->host,
            'username' => $this->username,
            'output' => $output,
        ];

        $mercure->publish(new Update($topic,
            json_encode($data, JSON_THROW_ON_ERROR),
        ));
    }
}
