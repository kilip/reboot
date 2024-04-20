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

use Reboot\Contracts\NodeScannerInterface;
use Reboot\Contracts\SftpInterface;
use Reboot\Contracts\SshInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class NodeScanner implements NodeScannerInterface
{
    private string $remoteResultFile;
    private string $localResultFile;

    public function __construct(
        private string $target,
        private SshInterface $ssh,
        private SftpInterface $sftp,
        private MessageBusInterface $messageBus,
        private string $commandTemplate = 'sudo nmap -sn -sP -oX {{result_file}} {{target}}',

        private ?ResultParser $resultParser = null,

        #[Autowire('%reboot.remote_path%/scan-nodes')]
        string $remotePath = '/tmp/reboot',

        #[Autowire('%reboot.cache_dir%/scan-nodes')]
        string $cachePath = '/tmp/reboot',
    ) {
        $this->remoteResultFile = $remotePath.DIRECTORY_SEPARATOR.Uuid::v1().'.xml';
        $this->localResultFile = $cachePath.DIRECTORY_SEPARATOR.Uuid::v1().'.xml';
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

        $ssh->addCommand('mkdir -p '.dirname($this->remoteResultFile));
        $ssh->addCommand($command);
        $ssh->execute();
    }

    /**
     * Copy nmap result from remote to local target.
     */
    private function copyResult(): void
    {
        // @codeCoverageIgnoreStart
        if (!is_dir($dir = dirname($this->localResultFile))) {
            mkdir($dir, 0777, true);
        }
        // @codeCoverageIgnoreEnd

        $this->sftp->downloadFile($this->remoteResultFile, $this->localResultFile);
    }

    private function parseResult(): void
    {
        $parser = $this->resultParser ?? new ResultParser();
        $hosts = $parser->parse($this->localResultFile);

        foreach ($hosts as $host) {
            $this->messageBus->dispatch($host);
        }
    }
}
