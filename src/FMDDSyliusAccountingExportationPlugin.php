<?php

declare(strict_types=1);

namespace FMDD\SyliusAccountingExportationPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FMDDSyliusAccountingExportationPlugin extends Bundle
{
    use SyliusPluginTrait;
}
