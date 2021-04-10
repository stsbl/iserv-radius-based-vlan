<?php
declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Vlan\Repository;

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

use Stsbl\RadiusVlanBundle\Entity\Vlan;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
interface VlanRepositoryInterface
{
    /**
     * Returns the highest used VLAN piority.
     */
    public function highestPriority(): ?int;

    /**
     * Returns the highest used VLAN piority.
     */
    public function lowestPriority(): ?int;

    /**
     * Returns the next VLAN according to priority.
     */
    public function nextVlan(Vlan $vlan): ?Vlan;

    /**
     * Returns the previous VLAN according to priority.
     */
    public function previousVlan(Vlan $vlan): ?Vlan;

    /**
     * Persists the given VLAN to the storage.
     */
    public function save(Vlan $vlan): void;
}
