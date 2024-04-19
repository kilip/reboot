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

use phpseclib3\Crypt\Common\AsymmetricKey;
use phpseclib3\Net\SSH2;
use Reboot\Contracts\SshInterface;
use Reboot\Tests\Bridge\SSH\SshException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class SSH implements SshInterface
{
    private string $currentCommand = '';

    /**
     * @param array<int, string> $commands
     */
    public function __construct(
        private readonly string $host,
        private readonly string $username,
        private readonly AsymmetricKey $privateKey,
        private readonly HubInterface $mercureHub,
        private readonly int $port = 22,
        private readonly int $timeout = 10,
        private array $commands = [],
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    public function addCommand(string $command): void
    {
        $this->commands[] = $command;
    }

    /**
     * @codeCoverageIgnore
     */
    public function execute(): void
    {
        $ssh = new SSH2($this->host, $this->port, $this->timeout);
        if (!$ssh->login($this->username, $this->privateKey)) {
            throw SshException::loginFailed(ipAddress: $this->host, username: $this->username);
        }
        foreach ($this->commands as $command) {
            $this->currentCommand = $command;
            $ssh->exec($command, [$this, 'onExecute']);
        }
    }

    public function onExecute(string $output): void
    {
        $url = $this->mercureHub->getPublicUrl().'/ssh';
        $data = [
            'host' => $this->host,
            'username' => $this->username,
            'command' => $this->currentCommand,
            'output' => $output,
        ];
        $update = new Update(
            $url,
            json_encode($data, JSON_THROW_ON_ERROR),
        );

        $this->mercureHub->publish($update);
    }
}
