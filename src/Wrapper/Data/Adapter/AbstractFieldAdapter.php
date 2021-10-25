<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Adapter;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;
use FINDOLOGIC\PlentyMarketsRestExporter\RegistryService;

abstract class AbstractFieldAdapter implements FieldAdapter
{
    private Config $config;
    private RegistryService $registryService;

    public function __construct(Config $config, RegistryService $registryService)
    {
        $this->config = $config;
        $this->registryService = $registryService;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getRegistryService(): RegistryService
    {
        return $this->registryService;
    }
}
