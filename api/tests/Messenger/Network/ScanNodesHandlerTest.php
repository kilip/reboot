<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Messenger\Network;

use PHPUnit\Framework\TestCase;
use Reboot\Contracts\NetworkFactoryInterface;
use Reboot\Contracts\NodeScannerInterface;
use Reboot\Enum\ScanModeEnum;
use Reboot\Messenger\Network\ScanNodesHandler;

class ScanNodesHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $factory = $this->createMock(NetworkFactoryInterface::class);
        $scanner = $this->createMock(NodeScannerInterface::class);
        $handler = new ScanNodesHandler($factory);

        $factory->expects($this->once())
            ->method('createNodeScanner')
            ->with('target')
            ->willReturn($scanner);

        $scanner->expects($this->once())
            ->method('run');

        $handler(new \Reboot\Messenger\Network\ScanNodesCommand('target', ScanModeEnum::ScanNodes));
    }
}
