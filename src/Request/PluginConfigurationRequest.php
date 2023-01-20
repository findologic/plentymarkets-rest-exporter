<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class PluginConfigurationRequest extends Request
{
    public function __construct(int $pluginId, int $pluginSetId)
    {
        parent::__construct('GET', "plugins/$pluginId/plugin_sets/$pluginSetId/configurations");
    }
}
