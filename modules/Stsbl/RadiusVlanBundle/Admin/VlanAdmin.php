<?php
declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Admin;

use IServ\CrudBundle\Crud\ServiceCrud;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use IServ\CrudBundle\Routing\RoutingDefinition;
use Stsbl\RadiusVlanBundle\Entity\Vlan;

/*
 * The MIT License
 *
 * Copyright 2019 Felix Jacobi.
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
final class VlanAdmin extends ServiceCrud
{
    public const TEMPLATE_PAGE = '@IServAdmin/page.html.twig';
    public const TEMPLATE_BASE = '@IServAdmin/Admin/base.html.twig';

    /**
     * {@inheritDoc}
     */
    protected static $entityClass = Vlan::class;

    // Set admin stuff
    // FIXME No ServiceAdmin base class yet

    /**
     * {@inheritDoc}
     */
    protected $templates = [
        'page' => self::TEMPLATE_PAGE,
        'crud_base' => self::TEMPLATE_BASE,
    ];

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->title = _('VLAN networks');
        $this->itemTitle = _('VLAN network');
        $this->options['sort'] = 'priority';
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('description', null, [
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
            ->add('priority', null, [
                'label' => _('Order'),
                'sortType' => 'natural',
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
            ])
            ->add('room', null, [
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
    public static function defineRoutes(): RoutingDefinition
    {
        $routes = parent::defineRoutes();

        // FIXME No ServiceAdmin base class yet
        $routes->setNamePrefix('admin_');
        $routes->setPathPrefix('/admin/');

        return $routes;
    }
}
