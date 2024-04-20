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

use Psr\Log\LoggerInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\NetworkFactoryInterface;
use Reboot\Contracts\NodeScannerInterface;
use Reboot\Contracts\NodeStatusUpdaterInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Tasks\UpdateNodeStatusTask;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class NetworkFactory implements NetworkFactoryInterface
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private SshFactoryInterface $sshFactory,
        private MessageBusInterface $messageBus,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function createNodeScanner(string $target): NodeScannerInterface
    {
        $this->logger?->notice('Start creating node scanner');

        $nodeRepository = $this->nodeRepository;
        $sshFactory = $this->sshFactory;
        $node = $nodeRepository->getNavigator();

        $this->logger?->notice('Using navigator {0}', [$node->getHostname()]);
        $ssh = $sshFactory->createSshClient($node);
        $sftp = $sshFactory->createSftpClient($node);

        return new NodeScanner(
            target: $target,
            ssh: $ssh,
            sftp: $sftp,
            messageBus: $this->messageBus,
        );
    }

    public function createNodeStatusUpdater(): NodeStatusUpdaterInterface
    {
        return new UpdateNodeStatusTask(
            $this->nodeRepository,
            $this->messageBus
        );
    }
}
