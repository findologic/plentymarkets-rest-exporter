<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Parser\Parser;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemPropertiesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ItemPropertiesParser extends Parser
{
    /**
     * @return ItemPropertiesResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $properties = [];
        foreach ($response['entries'] as $property) {
            $properties[] = new ItemProperty($property);
        }

        return new ItemPropertiesResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $properties,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
