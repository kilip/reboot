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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WakeOnLanHandler
{
    public function __construct(
        private readonly NodeRepositoryInterface $nodeRepository,

        private readonly SshFactoryInterface $sshFactory,

        #[Autowire('%env(WAKEONLAN_HOST_EXECUTOR)%')]
        private readonly string $executorTarget = 'localhost'
    ) {
    }

    public function __invoke(WakeOnLanCommand $command): void
    {
        $nodeRepository = $this->nodeRepository;
        $sshFactory = $this->sshFactory;

        $node = $nodeRepository->findByIpAddress($this->executorTarget);
        $ssh = $sshFactory->create($node);
        $node = $nodeRepository->findById($command->getNodeId());

        $ssh->addCommand("wakeonlan {$node->getMacAddress()}");
        $ssh->execute();
    }
}