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
use phpseclib3\Crypt\PublicKeyLoader;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\SshFactoryInterface;
use Reboot\Contracts\SshInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mercure\HubInterface;

final readonly class SshFactory implements SshFactoryInterface
{
    public function __construct(
        private HubInterface $mercureHub,

        #[Autowire('%env(resolve:SSH_PRIVATE_KEY)%')]
        private string $defaultPrivateKey = '',

        #[Autowire('%env(resolve:SSH_DEFAULT_USER)%')]
        private string $defaultUser = ''
    ) {
    }

    public function create(NodeInterface $node): SshInterface
    {
        $key = $this->loadKey($node->getSshPrivateKey() ?? $this->defaultPrivateKey);

        return new SSH(
            host: $node->getIpAddress(),
            username: $node->getSshUser() ?? $this->defaultUser,
            privateKey: $key,
            mercureHub: $this->mercureHub,
            port: $node->getSshPort() ?? 22
        );
    }

    private function loadKey(string $privateKey): AsymmetricKey
    {
        $contents = $privateKey;
        if (is_file($privateKey)) {
            $contents = file_get_contents($privateKey);
        }

        return PublicKeyLoader::load($contents);
    }
}
