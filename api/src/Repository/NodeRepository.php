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

use ApiPlatform\Doctrine\Orm\AbstractPaginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Entity\Node;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends ServiceEntityRepository<NodeInterface>
 */
class NodeRepository extends ServiceEntityRepository implements NodeRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        #[Autowire('%env(resolve:SSH_NAVIGATOR)%')]
        private string $navigator
    ) {
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

    public function getTotal(): int
    {
        $qb = $this->createQueryBuilder('node');
        $qb->select('COUNT(node.id)')
            ->where($qb->expr()->eq('node.draft', ':draft'))
            ->setParameter('draft', false);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array<string,string> $criteria
     *
     * @return Paginator<Node>|AbstractPaginator<Node>
     */
    public function getPaginator(int $firstResult, int $itemPerPage, array $criteria = []): Paginator|AbstractPaginator
    {
        $qb = $this->createQueryBuilder('node');
        $qb->where($qb->expr()->eq('node.draft', ':draft'))
            ->setParameter(':draft', false)
        ;

        $query = $qb->getQuery()
            ->setFirstResult($firstResult)
            ->setMaxResults($itemPerPage);

        return new Paginator($query, false);
    }

    public function getNavigator(): NodeInterface
    {
        if (!is_null($node = $this->findByIpAddress($this->navigator))) {
            return $node;
        }

        if (!is_null($node = $this->findOneBy(['hostname' => $this->navigator]))) {
            return $node;
        }
        throw new \Exception('Can not find navigator to use: '.$this->navigator);
    }

    public function store(NodeInterface $node): void
    {
        $this->getEntityManager()->persist($node);
        $this->getEntityManager()->flush();
    }

    public function findByMacAddress(string $macAddress): ?NodeInterface
    {
        return $this->findOneBy(['macAddress' => $macAddress]);
    }

    public function findByHostname(string $hostName): ?NodeInterface
    {
        return $this->findOneBy(['hostname' => $hostName]);
    }

    public function create(): NodeInterface
    {
        return new Node();
    }
}
