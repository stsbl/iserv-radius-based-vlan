<?php
declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Admin\Batch;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CrudBundle\Crud\Batch\AbstractBatchAction;
use IServ\CrudBundle\Entity\FlashMessageBag;
use Stsbl\RadiusVlanBundle\Admin\VlanAdmin;
use Stsbl\RadiusVlanBundle\Vlan\VlanManager;

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
final class SwapVlanAction extends AbstractBatchAction
{
    /**
     * @var VlanAdmin
     */
    protected $crud;

    /**
     * @var VlanManager
     */
    private $vlanManager;

    /**
     * @var bool
     */
    private $moveUp;

    public function __construct(VlanAdmin $crud, VlanManager $vlanManager, bool $moveUp, bool $enabled = true)
    {
        parent::__construct($crud, $enabled);

        $this->vlanManager = $vlanManager;
        $this->moveUp = $moveUp;
    }

    /**
     * {@inheritDoc}
     */
    public function getListIcon(): string
    {
        return $this->moveUp ? 'chevron-up' : 'chevron-down';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return $this->moveUp ? _('Move VLAN up in list') : _('Move VLAN down in list');
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->moveUp ? 'vlan-move-up' : 'vlan-move-down';
    }

    /**
     * {@inheritDoc}
     */
    public function requiresConfirmation(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ArrayCollection $entities): FlashMessageBag
    {
        $flashMessages = new FlashMessageBag();

        if ($entities->count() > 1) {
            $flashMessages->addWarning(_('You can only move one VLAN at a time.'));

            return $flashMessages;
        }

        foreach ($entities as $vlan) {
            if ($this->moveUp) {
                $this->vlanManager->moveUp($vlan);
                $flashMessages->addSuccess(__('Moved VLAN %s up in list.', $vlan));
            } else {
                $this->vlanManager->moveDown($vlan);
                $flashMessages->addSuccess(__('Moved VLAN %s down in list.', $vlan));
            }
        }

        $this->crud->onChange();

        return $flashMessages;
    }
}
