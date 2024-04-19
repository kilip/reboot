<?php

namespace Reboot\Messenger\Node;

class NodeCommandException extends \Exception
{
    public static function wakeOnLanExecutorNotExists(string $executorHost): self
    {
        return new self(sprintf(
            'Wake On Lan executor host "%s" not exists',
            $executorHost
        ));
    }
}
