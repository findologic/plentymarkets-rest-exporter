<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Parser;

use FINDOLOGIC\PlentyMarketsRestExporter\Response\Collection\ManufacturerResponse;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity\Manufacturer;
use FINDOLOGIC\PlentyMarketsRestExporter\Response\Response;
use Psr\Http\Message\ResponseInterface;

class ManufacturerParser extends Parser
{
    public static function parse(ResponseInterface $rawResponse): ManufacturerResponse
    {
        $response = self::unserializeJsonResponse($rawResponse);

        $manufacturers = [];
        foreach ($response['entries'] as $manufacturer) {
            $manufacturers[] = new Manufacturer($manufacturer);
        }

        return new ManufacturerResponse(
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
