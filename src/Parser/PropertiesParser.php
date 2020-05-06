<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertiesResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class PropertiesParser extends Parser
{
    /**
     * @return PropertiesResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $properties = [];
        foreach ($response['entries'] as $property) {
            $properties[] = new Property($property);
        }

        return new PropertiesResponse(
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
