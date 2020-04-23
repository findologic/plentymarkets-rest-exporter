<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ManufacturersResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Manufacturer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ManufacturersParser extends Parser
{
    /**
     * @return ManufacturersResponse
     */
    public static function parse(ResponseInterface $rawResponse): Response
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $manufacturers = [];
        foreach ($response['entries'] as $manufacturer) {
            $manufacturers[] = new Manufacturer($manufacturer);
        }

        return new ManufacturersResponse(
            $response['page'],
            $response['totalsCount'],
            $response['isLastPage'],
            $manufacturers,
            $response['lastPageNumber'],
            $response['firstOnPage'],
            $response['lastOnPage'],
            (int)$response['itemsPerPage']
        );
    }
}
