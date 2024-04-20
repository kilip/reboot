<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Reboot\Controller\ScanNodesAction;
use Reboot\Messenger\Network\ScanNodesCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ScanNodesActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $request = $this->createMock(Request::class);
        $action = new ScanNodesAction($messageBus);

        $request->expects($this->once())
            ->method('toArray')
            ->willReturn(['target' => 'test']);

        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ScanNodesCommand::class))
            ->willReturn(new Envelope(new \stdClass()));

        $action($request);
    }
}
