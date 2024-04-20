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

use Psr\Log\LoggerInterface;
use Reboot\Bridge\Network\NetworkException;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Reboot\Messenger\Network\UpdateUptimeRequest;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateUptimeHandler
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private SshFactoryInterface $sshFactory,
        private LoggerInterface $logger
    ){
    }

    public function __invoke(UpdateUptimeRequest $request): void
    {
        $nodeRepository = $this->nodeRepository;
        $node = $nodeRepository->findById($request->getNodeId());

        if(!$node instanceof NodeInterface){
            throw NetworkException::uptimeNodeNotExists($request->getNodeId());
        }

        $ssh = $this->sshFactory->createSshClient($node);

        $ssh->addCommand('uptime -s');
        $ssh->execute();

        $outputs = $ssh->getOutputs();
        $strdate = $outputs[0];

        $uptime = date_create_immutable_from_format('Y-m-d H:i:s', $strdate, new \DateTimeZone('Asia/Makassar'));

        $this->logger->notice('setting uptime for node {0} with "{1}"', [$node->getHostname(), $uptime]);

        $node->setUptime($uptime);
        $nodeRepository->store($node);

    }
}
