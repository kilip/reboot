<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\SSH;

class SshException extends \Exception
{
    public static function failedToLogin(string $host, string $username, int $port): self
    {
        return new self(sprintf(
            'Failed login to host "%s:%s" with username "%s"',
            $host,
            $port,
            $username
        ));
    }

    public static function failedToExecuteCommand(string $currentCommand, string $message): self
    {
        return new self(sprintf(
            'Failed to execute command "%s", Error message: "%s"',
            $currentCommand,
            $message
        ));
    }

    public static function failedToDownloadFile(string $host, string $username, int $port, string $remoteResultFile, string $localResultFile, string $message): self
    {
        return new self(sprintf(
            'Failed to download file from %s@%s:%s:%s to %s. Error: %s',
            $username,
            $host,
            $port,
            $remoteResultFile,
            $localResultFile,
            $message
        ));
    }
}
