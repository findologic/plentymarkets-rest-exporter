<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertyGroupResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\PropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class PropertyGroupParser extends Parser
{
    /**
     * @return PropertyGroupResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $propertyGroups = [];
        foreach ($response['entries'] as $propertyGroup) {
            $propertyGroups[] = new PropertyGroup($propertyGroup);
        }

        return new PropertyGroupResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $propertyGroups,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
