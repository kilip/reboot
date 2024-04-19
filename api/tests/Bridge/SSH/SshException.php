<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Bridge\SSH;

class SshException extends \Exception
{
    public static function loginFailed(string $ipAddress, string $username): self
    {
        return new self(sprintf(
            'Failed to login to "%s@%s"',
            $username,
            $ipAddress
        ));
    }
}
