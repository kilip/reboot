<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Entity\Node;

/**
 * @extends ServiceEntityRepository<NodeInterface>
 */
class NodeRepository extends ServiceEntityRepository implements NodeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Node::class);
    }

    public function findById(string $id): ?NodeInterface
    {
        return $this->find($id);
    }

    public function findByIpAddress(string $ipAddress): ?NodeInterface
    {
        return $this->findOneBy(['ipAddress' => $ipAddress]);
    }
}
