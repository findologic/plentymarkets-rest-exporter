<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PluginConfigurationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PluginConfiguration;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class PluginConfigurationParser extends Parser
{
    /**
     * @param ResponseInterface $rawResponse
     * @return PluginConfigurationResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $rawConfigurations = self::unserializeJsonResponse($rawResponse);

        $configurations = [];
        foreach ($rawConfigurations as $rawConfiguration) {
            $configurations[] = new PluginConfiguration($rawConfiguration);
        }

        return new PluginConfigurationResponse($configurations);
    }
}
