<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Messenger\Node;

use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SshFactoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
final readonly class RebootHandler
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private SshFactoryInterface $sshFactory
    ) {
    }

    public function __invoke(RebootCommand $command): void
    {
        $node = $this->nodeRepository->findById($command->getNodeId());
        $ssh = $this->sshFactory->createSshClient($node);

        $ssh->addCommand('sudo reboot');
        $ssh->execute();
    }
}
