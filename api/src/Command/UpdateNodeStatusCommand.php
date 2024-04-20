<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Reboot\Command;

use Reboot\Contracts\NetworkFactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'reboot:update', description: 'Update node status')]
class UpdateNodeStatusCommand extends Command
{
    public function __construct(
        private NetworkFactoryInterface $factory
    ) {
        parent::__construct('reboot:update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updater = $this->factory->createNodeStatusUpdater();
        $updater->run();

        return Command::SUCCESS;
    }
}
