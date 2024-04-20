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
use Reboot\Contracts\NetworkFactoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
final readonly class ScanNodesHandler
{
    public function __construct(
        private NetworkFactoryInterface $networkScannerFactory,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function __invoke(ScanNodesCommand $command): void
    {
        $this->logger?->notice('Creating scanner for target {0}', [$command->getTarget()]);
        $scanner = $this->networkScannerFactory->createNodeScanner($command->getTarget());
        $this->logger?->notice('created node scanner');
        $scanner->run();
    }
}
