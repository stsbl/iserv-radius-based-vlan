<?php
declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Vlan;

use Stsbl\RadiusVlanBundle\Entity\Vlan;
use Stsbl\RadiusVlanBundle\Vlan\Repository\VlanRepositoryInterface;

/*
 * The MIT License
 *
 * Copyright 2020 Felix Jacobi.
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
final class VlanManager
{
    /**
     * @var VlanRepositoryInterface
     */
    private $repository;

    public function __construct(VlanRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Swaps the priority of the current the given VLAN with the previous one.
     */
    public function moveUp(Vlan $vlan): void
    {
        self::validateVlan($vlan);

        if ($vlan->getPriority() === $this->repository->lowestPriority()) {
            // VLAN has already the lowest priority => nothing to do
            return;
        }

        $previousVlan = $this->repository->previousVlan($vlan);

        if (null === $previousVlan) {
            return;
        }

        $this->swapVlanPriority($vlan, $previousVlan);
    }

    /**
     * Swaps the priority of the current the given VLAN with the previous one.
     */
    public function moveDown(Vlan $vlan): void
    {
        self::validateVlan($vlan);

        if ($vlan->getPriority() === $this->repository->highestPriority()) {
            // VLAN has already the highest priority => nothing to do
            return;
        }

        $nextVlan = $this->repository->nextVlan($vlan);

        if (null === $nextVlan) {
            return;
        }

        $this->swapVlanPriority($vlan, $nextVlan);
    }

    /**
     * Returns the next free VLAN.
     */
    public function getNextFreePriority(): int
    {
        if (null === $highestPriority = $this->repository->highestPriority()) {
            return 0;
        }

        return $highestPriority + 1;
    }

    private static function validateVlan(Vlan $vlan): void
    {
        if (null === $vlan->getId()) {
            throw new \LogicException('A VLAN must have a set database ID to allow swapping of it. Did you forget to persist the entity properly?');
        }

        if (null === $vlan->getVlanId()) {
            throw new \LogicException('A VLAN must have a set VLAN ID to allow swapping of it. Did you forget to persist the entity properly?');
        }
    }

    private function swapVlanPriority(Vlan $vlan, Vlan $nextVlan): void
    {
        $highestPriority = $this->repository->highestPriority();
        $newPriority = $nextVlan->getPriority();
        $oldPriority = $vlan->getPriority();

        // Move next VLAN temporary to end of list, to free current priority
        $nextVlan->setPriority($highestPriority + 1);

        $this->repository->save($nextVlan);

        // Swap priority
        $nextVlan->setPriority($oldPriority);
        $vlan->setPriority($newPriority);


        $this->repository->save($vlan);
        $this->repository->save($nextVlan);
    }
}
