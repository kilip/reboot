<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\Network\Messenger;

use Reboot\Bridge\Network\ResultParser;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SftpInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Network\NodeStatusUpdateRequest;
use Reboot\Messenger\Network\UpdateUptimeRequest;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler(fromTransport: 'async')]
final class NodeStatusUpdateHandler
{
    private SshInterface $ssh;
    private SftpInterface $sftp;

    private string $localResult;
    private string $remoteResult;

    public function __construct(
        private readonly NodeRepositoryInterface $nodeRepository,
        SshFactoryInterface $sshFactory,
        private readonly MessageBusInterface $messageBus,

        #[Autowire('%reboot.remote_path%/network/status')]
        string $remoteTempPath,

        #[Autowire('%reboot.cache_dir%/network/status')]
        string $cachePath,

        private ?ResultParser $resultParser = null,
    ) {
        $navigator = $this->nodeRepository->getNavigator();
        $this->ssh = $sshFactory->createSshClient($navigator);
        $this->sftp = $sshFactory->createSftpClient($navigator);
        $this->localResult = $cachePath.DIRECTORY_SEPARATOR.Uuid::v1().'.xml';
        $this->remoteResult = $remoteTempPath.DIRECTORY_SEPARATOR.Uuid::v1().'.xml';

        if (is_null($this->resultParser)) {
            $this->resultParser = new ResultParser();
        }
    }

    public function __invoke(NodeStatusUpdateRequest $request): void
    {
        $paginator = $this->nodeRepository->getPaginator(
            firstResult: $request->getFirstResult(),
            itemPerPage: $request->getPageSize(),
        );

        $nodes = [];
        foreach ($paginator as $node) {
            $nodes[$node->getIpAddress()] = $node;
        }

        $this->executeNmapCommand($nodes);
        $this->downloadResultFile();
        $this->parseResultFile($nodes);
    }

    /**
     * @param array<string, NodeInterface> $nodes
     */
    private function executeNmapCommand(array $nodes): void
    {
        $ips = array_keys($nodes);
        $implode = implode(' ', $ips);
        $ssh = $this->ssh;
        $command = "sudo nmap -sn -n -oX {$this->remoteResult} {$implode}";

        $ssh->addCommand('sudo mkdir -p '.dirname($this->remoteResult));
        $ssh->addCommand($command);
        $ssh->execute();
    }

    private function downloadResultFile(): void
    {
        // @codeCoverageIgnoreStart
        if (!is_dir($dir = dirname($this->localResult))) {
            mkdir($dir, 0777, true);
        }
        // @codeCoverageIgnoreEnd

        $this->sftp->downloadFile($this->remoteResult, $this->localResult);
    }

    /**
     * @param array<string,NodeInterface> $nodes
     */
    private function parseResultFile(array $nodes): void
    {
        $parser = $this->resultParser;
        $parser->parse($this->localResult);
        $onlineIps = $parser->getOnlineIps();

        foreach ($nodes as $ip => $node) {
            $online = false;
            if (in_array($ip, $onlineIps)) {
                $online = true;
            }
            $node->setOnline($online);

            if ($online && is_null($node->getUptime())) {
                $msg = new UpdateUptimeRequest($node->getId());
                $this->messageBus->dispatch($msg);
            }
            if (false === $online) {
                $node->setUptime(null);
            }

            $this->nodeRepository->store($node);
        }
    }
}
