<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Controller;

use Reboot\Enum\ScanModeEnum;
use Reboot\Messenger\Node\ScanNodesCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class ScanNodesAction
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[Route('/scan-nodes', name: 'scan-nodes', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): JsonResponse {
        $json = $request->toArray();
        $target = $json['target'];
        $command = new ScanNodesCommand($target, ScanModeEnum::ScanNodes);

        $this->messageBus->dispatch($command);

        return new JsonResponse(['message' => 'ok']);
    }
}
