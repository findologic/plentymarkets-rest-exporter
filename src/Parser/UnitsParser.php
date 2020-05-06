<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\UnitsResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Unit;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class UnitsParser extends Parser
{
    /**
     * @return UnitsResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $units = [];
        foreach ($response['entries'] as $unit) {
            $units[] = new Unit($unit);
        }

        return new UnitsResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $units,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
