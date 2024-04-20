<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Contracts\Entity;

use ApiPlatform\Doctrine\Orm\AbstractPaginator;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface NodeRepositoryInterface
{
    public function findById(string $id): ?NodeInterface;

    public function findByIpAddress(string $ipAddress): ?NodeInterface;

    public function getTotal(): int;

    /**
     * @param array<string,string> $criteria
     */
    public function getPaginator(int $firstResult, int $itemPerPage, array $criteria = []): Paginator|AbstractPaginator;

    public function store(NodeInterface $node): void;

    public function getNavigator(): NodeInterface;
}
