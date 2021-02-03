<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Request;

class PluginFromSetRequest extends Request
{
    public function __construct(int $pluginSetId)
    {
        parent::__construct('GET', "plugin_sets/{$pluginSetId}/plugins");
    }
}
