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
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class SSH implements SshInterface
{
    private string $currentCommand = '';

    /**
     * @var array<int, string>
     */
    private array $outputs = [];

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
            $this->publishOutput(sprintf(
                'Failed ssh login to host "%s" with username: "%s"',
                $this->host,
                $this->username
            ));
        }

        try {
            foreach ($this->commands as $command) {
                $this->currentCommand = $command;
                $ssh->exec($command, [$this, 'publishOutput']);
            }
        } catch (\Exception $exception) {
            $this->publishOutput($exception->getMessage());
        }

        $ssh->disconnect();
    }

    public function publishOutput(string $output): void
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

        $this->outputs[] = $output;
    }

    /**
     * @return array<int,string>
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }
}
