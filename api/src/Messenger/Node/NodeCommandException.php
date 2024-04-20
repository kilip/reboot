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

class NodeCommandException extends \Exception
{
    public static function wakeOnLanExecutorNotExists(string $executorHost): self
    {
        return new self(sprintf(
            'Wake On Lan executor host "%s" not exists',
            $executorHost
        ));
    }

    public static function powerOffNodeNotExists(string $nodeId): self
    {
        return new self(sprintf(
            'Power off node "%s" not exists',
            $nodeId
        ));
    }
}
