<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field\Requirement;

use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;

trait RegistryAware
{
    protected ?RegistryService $registryService;

    public function setRegistryService(RegistryService $registryService): void
    {
        $this->registryService = $registryService;
    }
}
