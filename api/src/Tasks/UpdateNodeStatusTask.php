<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tasks;

use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\NodeStatusUpdaterInterface;
use Reboot\Messenger\Network\NodeStatusUpdateRequest;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UpdateNodeStatusTask implements NodeStatusUpdaterInterface
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private MessageBusInterface $messageBus,
        private int $pageSize = 5
    ) {
    }

    public function run(): void
    {
        $nodeRepository = $this->nodeRepository;
        $pageSize = $this->pageSize;
        $totalItems = $nodeRepository->getTotal();
        $pagesCount = ceil($totalItems / $this->pageSize);

        for ($i = 0; $i < $pagesCount; ++$i) {
            $message = new NodeStatusUpdateRequest(
                $pageSize * $i,
                $pageSize
            );
            $this->messageBus->dispatch($message);
        }
    }
}
