<?php

namespace Reboot\Messenger\Node;

final readonly class NodeScanCommand
{
    /**
     * @param array<int,string> $targets
     */
    public function __construct(
        private array $targets
    )
    {
    }

    /**
     * @return array<int,string>
     */
    public function getTargets(): array
    {
        return $this->targets;
    }
}
