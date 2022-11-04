<?php

declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Vlan\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use IServ\CrudBundle\Doctrine\ORM\ServiceEntitySpecificationRepository;
use Stsbl\RadiusVlanBundle\Entity\Vlan;
use Webmozart\Assert\Assert;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class DoctrineVlanRepository extends ServiceEntitySpecificationRepository implements VlanRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vlan::class);
    }

    /**
     * {@inheritDoc}
     */
    public function highestPriority(): ?int
    {
        $query = $this->getEntityManager()->getConnection()->prepare('SELECT MAX(priority) AS max_priority FROM radius_vlan');

        try {
            $query->execute();

            return $query->fetchOne();
        } catch (Exception $e) {
            throw new \RuntimeException('Failed to fetch.', 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function lowestPriority(): ?int
    {
        $query = $this->getEntityManager()->getConnection()->prepare('SELECT MIN(priority) AS min_priority FROM radius_vlan');

        try {
            $result = $query->executeQuery()->fetchOne();

            if (null === $result) {
                return null;
            }

            Assert::numeric($result);

            return (int)$result;
        } catch (Exception $e) {
            throw new \RuntimeException('Failed to fetch.', 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function nextVlan(Vlan $vlan): ?Vlan
    {
        $qb = $this->createQueryBuilder('v');

        $qb
            ->select('v')
            ->where($qb->expr()->gt('v.priority', $vlan->getPriority()))
            ->orderBy('v.priority', 'ASC')
            ->setMaxResults(1)
        ;

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException|NoResultException $e) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function previousVlan(Vlan $vlan): ?Vlan
    {
        $qb = $this->createQueryBuilder('v');

        $qb
            ->select('v')
            ->where($qb->expr()->lt('v.priority', $vlan->getPriority()))
            ->orderBy('v.priority', 'DESC')
            ->setMaxResults(1)
        ;

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NonUniqueResultException|NoResultException $e) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save(Vlan $vlan): void
    {
        $this->getEntityManager()->persist($vlan);
        $this->getEntityManager()->flush();
    }
}
