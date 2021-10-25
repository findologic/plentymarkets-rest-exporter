<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\PimVariationResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Pim\Variation;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class PimVariationsParser extends Parser
{
    /**
     * @param ResponseInterface $rawResponse
     * @return PimVariationResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);


        $itemVariations = [];
        foreach ($response['entries'] as $itemVariation) {
            $itemVariations[] = new Variation($itemVariation);
        }

        return new PimVariationResponse(
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
