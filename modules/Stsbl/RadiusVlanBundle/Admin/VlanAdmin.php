<?php

declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Admin;

use IServ\AdminBundle\Admin\AdminServiceCrud;
use IServ\CrudBundle\Crud\Batch\DeleteAction;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use IServ\CrudBundle\Routing\RoutingDefinition;
use IServ\HostBundle\Events\HostEvents;
use Stsbl\RadiusVlanBundle\Admin\Batch\SwapVlanAction;
use Stsbl\RadiusVlanBundle\Entity\Vlan;
use Stsbl\RadiusVlanBundle\Security\Privilege;
use Stsbl\RadiusVlanBundle\Vlan\VlanManager;

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
final class VlanAdmin extends AdminServiceCrud
{
    /**
     * {@inheritDoc}
     */
    protected static $entityClass = Vlan::class;

    /**
     * {@inheritDoc}
     */
    public function prePersist(CrudInterface $object, array $previousData = null): void
    {
        /** @var Vlan $object */
        $object->setPriority($this->vlanManager()->getNextFreePriority());
    }

    /**
     * {@inheritDoc}
     */
    public function postPersist(CrudInterface $object, array $previousData = null): void
    {
        $this->onChange();
    }

    /**
     * {@inheritDoc}
     */
    public function postRemove(CrudInterface $object, array $previousData = null): void
    {
        // Delay newhosts on batch delete
        if ($this->getAction() !== self::ACTION_BATCH) {
            $this->onChange();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function postUpdate(CrudInterface $object, array $previousData = null): void
    {
        $this->onChange();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->title = _('VLAN networks');
        $this->itemTitle = _('VLAN network');
        $this->options['sort'] = 'priority';
        $this->templates['crud_index'] = '@StsblRadiusVlan/admin/vlan_index.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('description', null, [
                'label' => _('Description'),
                'responsive' => 'all',
            ])
            ->add('vlanId', null, [
                'label' => _('VLAN ID'),
                'responsive' => 'all',
            ])
            ->add('room', null, [
                'label' => _('Room'),
                'responsive' => 'min-tablet',
            ])
            ->add('ipRange', null, [
                'label' => _('IP range'),
                'responsive' => 'min-tablet',
            ])
            ->add('groups', null, [
                'label' => _('Groups'),
                'responsive' => 'desktop',
            ])
            ->add('roles', null, [
                'label' => _('Roles'),
                'responsive' => 'desktop',
            ])
            ->add('priority', null, [
                'label' => _('Order'),
                'sortType' => 'natural',
                'responsive' => 'desktop',
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('description', null, [
                'label' => _('Description'),
            ])
            ->add('vlanId', null, [
                'label' => _('VLAN ID'),
                'attr' => [
                    'help_text' => _('This VLAN ID will be sent via RADIUS if one of the given conditions below match.'),
                ]
            ])
            ->add('room', null, [
                'label' => _('Room'),
                'attr' => [
                    'help_text' => _('This room will be assigned to hosts of users whose signed-in in via RADIUS with the WLAN module and are a member of one role or group set here. If left out, the hosts will get the default room from system configuration, if set.'),
                ],
                'required' => false,
            ])
            ->add('ipRange', null, [
                'label' => _('IP range'),
                'attr' => [
                    'help_text' => _('Hosts in host management in this IP range will get the VLAN ID set here assigned on MAC-based RADIUS authentication.') . ' ' .
                        _('Additionally, for hosts of users whose are a member of one role or group set here and signed-in via RADIUS with the WLAN module an IP from that range.') . ' ' .
                        _('If left out, this VLAN is not accounted for MAC-based RADIUS authentication and hosts of RADIUS users will get an IP address from the range from system configuration.'),
                ],
                'required' => false,
            ])
            ->add('groups', null, [
                'label' => _('Groups'),
                'attr' => [
                    'help_text' => _('Match this VLAN only to members of at least one of these groups (does not have an effect on MAC-based RADIUS authentication).') . ' ' .
                        _('If neither a group or role set, this VLAN is not accounted for user-based RADIUS authentication.'),
                ]
            ])
            ->add('roles', null, [
                'label' => _('Roles'),
                'attr' => [
                    'help_text' => _('Match this VLAN only to members of at least one of these roles (does not have an effect on MAC-based RADIUS authentication).') . ' ' .
                        _('If neither a group or role set, this VLAN is not accounted for user-based RADIUS authentication.'),
                ]
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('description', null, [
                'label' => _('Description'),
            ])
            ->add('vlanId', null, [
                'label' => _('VLAN ID'),
            ])
            ->add('room', 'entity', [
                'label' => _('Room'),
            ])
            ->add('ipRange', null, [
                'label' => _('IP range'),
            ])
            ->add('groups', null, [
                'label' => _('Groups'),
            ])
            ->add('roles', null, [
                'label' => _('Roles'),
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthorized(): bool
    {
        return $this->isGranted(Privilege::RADIUS_VLAN);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadBatchActions(): void
    {
        parent::loadBatchActions();

        $this->batchActions->get(DeleteAction::NAME)->setCallback([$this, 'onChange']);

        $this->batchActions->add(new SwapVlanAction($this, $this->vlanManager(), true));
        $this->batchActions->add(new SwapVlanAction($this, $this->vlanManager(), false));
    }

    public function onChange(): void
    {
        $this->eventDispatcher()->dispatch(new class {
        }, HostEvents::HOST_CHANGED);
    }

    /**
     * {@inheritDoc}
     */
    public static function defineRoutes(): RoutingDefinition
    {
        $routes = parent::defineRoutes();

        // FIXME No ServiceAdmin base class yet
        $routes->setNamePrefix('admin_');
        $routes->setPathPrefix('/admin/');

        return $routes;
    }

    private function vlanManager(): VlanManager
    {
        return $this->locator->get(VlanManager::class);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [VlanManager::class]);
    }
}
