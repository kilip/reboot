<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Messenger\Network;

use Psr\Log\LoggerInterface;
use Reboot\Bridge\Network\ResultNode;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class NodeFoundHandler
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ResultNode $resultNode): void
    {
        $node = $this->createNode($resultNode);
        $this->nodeRepository->store($node);
    }

    private function createNode(ResultNode $resultNode): NodeInterface
    {
        $repository = $this->nodeRepository;

        if (
            !is_null($resultNode->getMac())
            && !is_null($node = $repository->findByMacAddress($resultNode->getMac()))) {
            return $node;
        }

        if (!is_null($node = $repository->findByIpAddress($resultNode->getIpAddress()))) {
            return $node;
        }

        if (!is_null($node = $repository->findByHostname($resultNode->getHostname()))) {
            return $node;
        }

        $this->logger->notice('New node found with hostname {0}', [$resultNode->getHostname()]);
        $node = $repository->create();
        $node
            ->setHostname($resultNode->getHostname())
            ->setIpAddress($resultNode->getIpAddress())
            ->setMacAddress($resultNode->getMac())
            ->setDraft(true)
        ;

        return $node;
    }
}
