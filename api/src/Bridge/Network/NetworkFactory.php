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

use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\NetworkFactoryInterface;
use Reboot\Contracts\NodeScannerInterface;
use Reboot\Contracts\SshFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class NetworkFactory implements NetworkFactoryInterface
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private SshFactoryInterface $sshFactory,
        private MessageBusInterface $messageBus,

        #[Autowire('%env(resolve:SSH_NAVIGATOR)%')]
        private string $navigator
    ) {
    }

    public function createNodeScanner(string $target): NodeScannerInterface
    {
        $nodeRepository = $this->nodeRepository;
        $sshFactory = $this->sshFactory;
        $node = $nodeRepository->findByIpAddress($this->navigator);

        if (!$node instanceof NodeInterface) {
            throw NetworkException::navigatorNodeNotExists($this->navigator);
        }

        $ssh = $sshFactory->createSshClient($node);
        $sftp = $sshFactory->createSftpClient($node);

        return new Scanner(
            target: $target,
            ssh: $ssh,
            sftp: $sftp,
            messageBus: $this->messageBus,
        );
    }
}
