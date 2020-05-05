<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ItemVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\ItemVariation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ItemVariationParser extends Parser
{
    /**
     * @return ItemVariationResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $itemVariations = [];
        foreach ($response['entries'] as $itemVariation) {
            $itemVariations[] = new ItemVariation($itemVariation);
        }

        return new ItemVariationResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $itemVariations,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
