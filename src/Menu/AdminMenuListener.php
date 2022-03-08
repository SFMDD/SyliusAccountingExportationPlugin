<?php

namespace FMDD\SyliusAccountingExportationPlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminMenuListener
{
    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItem(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $reportsMenu = $menu->addChild('reports')->setLabel('fmdd_accounting_exportation.ui.reports');

        $reportsMenu
            ->addChild('fmdd_accounting_exportation_reports', ['route' => 'fmdd_sylius_accounting_exportation_admin_index'])
            ->setLabel('fmdd_accounting_exportation.ui.accounting_exportation')
            ->setLabelAttribute('icon', 'list alternate')
        ;
    }
}
