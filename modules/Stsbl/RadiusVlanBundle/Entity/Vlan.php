<?php
declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\Group;
use IServ\CoreBundle\Entity\Role;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\RoomBundle\Entity\Room;
use Stsbl\RadiusVlanBundle\Validator\Constraints as VlanAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

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
 *
 * @ORM\Entity(repositoryClass="Stsbl\RadiusVlanBundle\Vlan\Repository\DoctrineVlanRepository")
 * @ORM\Table(name="radius_vlan")
 * @DoctrineAssert\UniqueEntity(fields={"description", "priority"})
 */
class Vlan implements CrudInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false, unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int|null
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="text", nullable=false, unique=false)
     *
     * @var string|null
     */
    private $description;

    /**
     * @Assert\NotBlank()
     * @Assert\Range(min=1, max=4095, invalidMessage="The VLAN ID must be a value between 1 and 4095.")
     * @ORM\Column(name="vlan_id", type="integer", nullable=false, unique=false)
     *
     * @var int|null
     */
    private $vlanId;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="priority", type="integer", nullable=false, unique=true)
     *
     * @var int
     */
    private $priority = 0;

    /**
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=true, unique=false)
     * @ORM\ManyToOne(targetEntity="IServ\RoomBundle\Entity\Room")
     *
     * @var Room|null
     */
    private $room;

    /**
     * @VlanAssert\IpRange(version="4")
     * @ORM\Column(name="ip_range", type="inet", nullable=true, unique=false)
     *
     * @var string|null
     */
    private $ipRange;

    /**
     * @ORM\JoinTable(name="radius_vlan_group", joinColumns={
     *        @ORM\JoinColumn(name="vlan_id", referencedColumnName="id", nullable=false, unique=false)
     *     }, inverseJoinColumns={
     *        @ORM\JoinColumn(name="grp", referencedColumnName="act", nullable=false, unique=false)
     *     }
     * )
     * @ORM\ManyToMany(targetEntity="IServ\CoreBundle\Entity\Group")
     * @var Group[]|Collection
     */
    private $groups;

    /**
     * @ORM\JoinTable(name="radius_vlan_role", joinColumns={
     *         @ORM\JoinColumn(name="vlan_id", referencedColumnName="id", nullable=false, unique=false)
     *     }, inverseJoinColumns={
     *         @ORM\JoinColumn(name="role", referencedColumnName="role", nullable=false, unique=false)
     *     }
     * )
     * @ORM\ManyToMany(targetEntity="IServ\CoreBundle\Entity\Role")
     * @var Role[]|Collection
     */
    private $roles;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->description ?? '?';
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getVlanId(): ?int
    {
        return $this->vlanId;
    }

    public function setVlanId(?int $vlanId): self
    {
        $this->vlanId = $vlanId;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;
        return $this;
    }

    public function getIpRange(): ?string
    {
        return $this->ipRange;
    }

    public function setIpRange(?string $ipRange): self
    {
        $this->ipRange = $ipRange;

        return $this;
    }

    public function addGroup(Group $group): self
    {
        $this->groups->add($group);

        return$this;
    }

    public function removeGroup(Group $group): self
    {
        $this->groups->removeElement($group);

        return$this;
    }

    /**
     * @return Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addRole(Role $role): self
    {
        $this->roles->add($role);

        return$this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);

        return$this;
    }

    /**
     * @return Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }
}
