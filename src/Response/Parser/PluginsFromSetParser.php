<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PluginFromSetResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Plugin;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class PluginsFromSetParser extends Parser
{
    /**
     * @param ResponseInterface $rawResponse
     * @return PluginFromSetResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $rawPlugins = self::unserializeJsonResponse($rawResponse);

        $plugins = [];
        foreach ($rawPlugins as $rawPlugin) {
            $plugins[] = new Plugin($rawPlugin);
        }

        return new PluginFromSetResponse($plugins);
    }
}
