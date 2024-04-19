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

use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Tests\Bridge\Network\SftpInterface;

interface SshFactoryInterface
{
    public function createSshClient(NodeInterface $node): SshInterface;

    public function createSftpClient(NodeInterface $node): SftpInterface;
}
