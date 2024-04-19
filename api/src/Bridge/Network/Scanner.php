<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\Network;

use Reboot\Contracts\NetworkScannerInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Node\NodeFoundNotification;
use Reboot\Tests\Bridge\Network\SftpInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class Scanner implements NetworkScannerInterface
{
    public function __construct(
        private string $target,
        private SshInterface $ssh,
        private SftpInterface $sftp,
        private MessageBusInterface $messageBus,
        private string $remoteResultFile = '/tmp/reboot/scanner/result.xml',
        private string $localResultFile = '/tmp/reboot/scanner/result.xml',
        private string $commandTemplate = 'nmap -sn -sP -oX {{result_file}} {{target}}'
    ) {
    }

    public function run(): void
    {
        $this->executeNmapCommand();
        $this->copyResult();
        $this->parseResult();
    }

    private function executeNmapCommand(): void
    {
        $command = strtr($this->commandTemplate, [
            '{{target}}' => $this->target,
            '{{result_file}}' => $this->remoteResultFile,
        ]);
        $ssh = $this->ssh;

        $ssh->addCommand('mkdir -p '.$this->remoteResultFile);
        $ssh->addCommand($command);
        $ssh->execute();
    }

    /**
     * Copy nmap result from remote to local target.
     */
    private function copyResult(): void
    {
        if (!is_dir($dir = dirname($this->localResultFile))) {
            mkdir($dir, 0777, true);
        }

        $this->sftp->get($this->remoteResultFile, $this->localResultFile);
    }

    private function parseResult(): void
    {
        $resultFile = $this->localResultFile;

        if (!is_file($this->localResultFile)) {
            throw NetworkException::scanResultFileNotExists($resultFile);
        }

        // $contents = file_get_contents($resultFile);
        $xml = simplexml_load_file($resultFile);
        $data = json_decode(json_encode($xml), true);
        $hosts = [];

        foreach ($data['host'] as $host) {
            $host = $this->createResultNode($host);
            $this->messageBus->dispatch($host);
        }
    }

    /**
     * @param array<string,mixed> $host
     */
    private function createResultNode(array $host): NodeFoundNotification
    {
        $ip = null;
        $mac = null;
        $vendor = null;
        $hostname = $host['hostnames']['hostname']['@attributes']['name'];

        // parse net address
        foreach ($host['address'] as $address) {
            if (!array_key_exists('@attributes', $address)) {
                $attr = $address;
            } else {
                $attr = $address['@attributes'];
            }

            $type = $attr['addrtype'];

            if ('ipv4' === $type) {
                $ip = $attr['addr'];
            }
            if ('mac' === $type) {
                $mac = $attr['addr'];
            }
            if (array_key_exists('vendor', $attr)) {
                $vendor = $attr['vendor'];
            }
        }

        return new NodeFoundNotification(
            ipAddress: $ip,
            hostname: $hostname,
            vendor: $vendor,
            macAddress: $mac
        );
    }
}
