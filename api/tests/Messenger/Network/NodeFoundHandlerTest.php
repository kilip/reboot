<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Messenger\Network;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Reboot\Bridge\Network\ResultNode;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Messenger\Network\NodeFoundHandler;

class NodeFoundHandlerTest extends TestCase
{
    private MockObject|NodeRepositoryInterface $nodeRepository;
    private MockObject|LoggerInterface $logger;

    private MockObject|NodeInterface $node;

    private NodeFoundHandler $handler;

    protected function setUp(): void
    {
        $this->nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->node = $this->createMock(NodeInterface::class);

        $this->handler = new NodeFoundHandler(
            $this->nodeRepository,
            $this->logger
        );
    }

    /**
     * @dataProvider getTestInvokeData
     */
    public function testInvoke(
        string $findMethod,
        string $needle
    ): void {
        $this->nodeRepository->expects($this->once())
            ->method($findMethod)
            ->with($needle)
            ->willReturn($this->node);

        $this->nodeRepository->expects($this->once())
            ->method('store')
            ->with($this->node);

        $handler = $this->handler;
        $handler(new ResultNode('host', 'ip', 'mac'));
    }

    /**
     * @return array<int, mixed>
     */
    public function getTestInvokeData(): array
    {
        return [
            ['findByMacAddress', 'mac'],
            ['findByIpAddress', 'ip'],
            ['findByHostname', 'host'],
        ];
    }

    public function testInvokeWithNewNode(): void
    {
        $node = $this->node;
        $handler = $this->handler;

        $this->nodeRepository->expects($this->once())
            ->method('create')
            ->willReturn($node);

        $this->nodeRepository->expects($this->once())
            ->method('store')
            ->with($node);

        $this->node->expects($this->once())
            ->method('setHostname')
            ->with('host')
            ->willReturn($node);
        $this->node->expects($this->once())
            ->method('setIpAddress')
            ->with('ip')
            ->willReturn($node);
        $this->node->expects($this->once())
            ->method('setMacAddress')
            ->with('mac')
            ->willReturn($node);
        $this->node->expects($this->once())
            ->method('setDraft')
            ->with(true)
            ->willReturn($node);

        $handler(new ResultNode('host', 'ip', 'mac'));
    }
}
