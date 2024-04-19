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

use Reboot\Contracts\NetworkScannerFactoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
final readonly class ScanNodesHandler
{
    public function __construct(
        private NetworkScannerFactoryInterface $networkScannerFactory
    ) {
    }

    public function __invoke(ScanNodesCommand $command): void
    {
        $scanner = $this->networkScannerFactory->create($command->getTarget());
        $scanner->run();
    }
}
