<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Task;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Tasks\UpdateNodeStatusTask;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateNodeStatusTaskTest extends TestCase
{
    private MockObject|NodeRepositoryInterface $nodeRepository;
    private MockObject|MessageBusInterface $messageBus;
    private UpdateNodeStatusTask $updater;

    protected function setUp(): void
    {
        $this->nodeRepository = $this->createMock(NodeRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->updater = new \Reboot\Tasks\UpdateNodeStatusTask(
            $this->nodeRepository,
            $this->messageBus,
        );
    }

    public function testRun()
    {
        $this->nodeRepository->expects($this->once())
            ->method('getTotal')
            ->willReturn(10);
        $this->messageBus->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        $this->updater->run();
    }
}
