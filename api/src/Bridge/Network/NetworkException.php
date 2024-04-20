<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\Network;

class NetworkException extends \Exception
{
    public static function scanResultFileNotExists(string $resultFile): self
    {
        return new self(sprintf(
            'Scanner result file "%s" not exists.',
            $resultFile
        ));
    }

    public static function navigatorNodeNotExists(string $navigator): self
    {
        return new self(sprintf(
            'Navigator node "%s" not exists.',
            $navigator
        ));
    }
}
