<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Controller\Node\WakeOnLanAction;
use Reboot\Enum\NodeTypeEnum;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new Post(
            security: "is_granted('ROLE_ADMIN')",
        ),
        new GetCollection(),
        new Get(),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Get(
            uriTemplate: '/nodes/{id}/wakeonlan',
            controller: WakeOnLanAction::class,
            security: "is_granted('ROLE_ADMIN')",
            name: 'wakeonlan'
        ),
    ]
)]
#[ORM\Entity]
class Node implements NodeInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    private string $hostname;

    #[ORM\Column(type: 'string', unique: true)]
    private string $ipAddress;

    #[ORM\Column(type: 'string', length: 20)]
    private string $macAddress;

    #[ORM\Column(type: 'boolean')]
    private bool $online = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $sshPrivateKey;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private string $sshUser;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $sshPort;

    #[ORM\Column(type: 'integer', enumType: NodeTypeEnum::class)]
    private NodeTypeEnum $type = NodeTypeEnum::Unknown;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): Node
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): Node
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getMacAddress(): string
    {
        return $this->macAddress;
    }

    public function setMacAddress(string $macAddress): Node
    {
        $this->macAddress = $macAddress;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): Node
    {
        $this->online = $online;

        return $this;
    }

    public function getSshPrivateKey(): string
    {
        return $this->sshPrivateKey;
    }

    public function setSshPrivateKey(string $sshPrivateKey): Node
    {
        $this->sshPrivateKey = $sshPrivateKey;

        return $this;
    }

    public function getSshUser(): string
    {
        return $this->sshUser;
    }

    public function setSshUser(string $sshUser): Node
    {
        $this->sshUser = $sshUser;

        return $this;
    }

    public function getSshPort(): int
    {
        return $this->sshPort;
    }

    public function setSshPort(int $sshPort): Node
    {
        $this->sshPort = $sshPort;

        return $this;
    }

    public function getType(): NodeTypeEnum
    {
        return $this->type;
    }

    public function setType(NodeTypeEnum $type): Node
    {
        $this->type = $type;

        return $this;
    }
}