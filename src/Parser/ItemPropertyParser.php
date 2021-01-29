<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemPropertyResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemProperty;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ItemPropertyParser extends Parser
{
    /**
     * @return ItemPropertyResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $properties = [];
        foreach ($response['entries'] as $property) {
            $properties[] = new ItemProperty($property);
        }

        return new ItemPropertyResponse(
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
