<?php

namespace Reboot\Messenger\Node;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class NodeScanHandler
{
    public function __construct(
        HubInterface $mercureHub
    )
    {}

    public function __invoke(NodeScanCommand $cmd)
    {
    }
}
