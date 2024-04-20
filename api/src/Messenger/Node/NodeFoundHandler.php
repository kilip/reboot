<?php

namespace Reboot\Messenger\Node;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
class NodeFoundHandler
{
    public function __invoke(NodeFoundNotification $newNode): void
    {
        // TODO: Implement __invoke() method.
    }
}
