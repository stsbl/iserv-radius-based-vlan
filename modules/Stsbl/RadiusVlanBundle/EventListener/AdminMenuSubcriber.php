<?php
declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\EventListener;

use IServ\AdminBundle\Event\Events;
use IServ\AdminBundle\EventListener\AdminMenuListenerInterface;
use IServ\CoreBundle\Event\MenuEvent;
use Stsbl\RadiusVlanBundle\Security\Privilege;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
final class AdminMenuSubcriber implements EventSubscriberInterface, AdminMenuListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBuildAdminMenu(MenuEvent $event): void
    {
        if ($event->getAuthorizationChecker()->isGranted(Privilege::RADIUS_VLAN)) {
            $item = $event->getMenu(self::ADMIN_NETWORK)->addChild('vlan', [
                'route' => 'admin_vlan_index',
                'label' => _('VLAN networks'),
            ]);

            $item->setExtra('icon', 'switch-network');
            $item->setExtra('icon_style', 'fugue');
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [Events::MENU => 'onBuildAdminMenu'];
    }
}
