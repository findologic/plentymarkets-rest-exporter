<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemPropertyGroupResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemPropertyGroup;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ItemPropertyGroupParser extends Parser
{
    /**
     * @return ItemPropertyGroupResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $propertyGroups = [];
        foreach ($response['entries'] as $propertyGroup) {
            $propertyGroups[] = new ItemPropertyGroup($propertyGroup);
        }

        return new ItemPropertyGroupResponse(
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
