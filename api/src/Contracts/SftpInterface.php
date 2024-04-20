<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Contracts;

interface SftpInterface
{
    /**
     * @param string $remote      The remote source
     * @param string $destination The destination
     */
    public function downloadFile(string $remote, string $destination): void;
}
