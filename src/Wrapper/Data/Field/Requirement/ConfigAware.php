<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Wrapper\Data\Field\Requirement;

use FINDOLOGIC\PlentyMarketsRestExporter\Config;

trait ConfigAware
{
    protected Config $config;

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }
}
