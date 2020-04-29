<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PropertySelectionsResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Property\Selection;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class PropertySelectionsParser extends Parser
{
    /**
     * @return PropertySelectionsResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $selections = [];
        foreach ($response['entries'] as $selection) {
            $selections[] = new Selection($selection);
        }

        return new PropertySelectionsResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $selections,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
